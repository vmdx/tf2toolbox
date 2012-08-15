from collections import defaultdict

import tf2toolbox.metadata
from tf2toolbox.steamapi import get_schema


def bp_parse(bp, form, session_info):
  """
  Given a JSON representation of a backpack, the user's request form,
  and a copy of the user's session info (steamID, customURL, etc.), returns the following in template_info.

    * A BBCode representation of the backpack
    * A list of status on what parts of the backpack are being displayed
    * Errors if needed.

  """
  schema = get_schema()
  if not schema:
    raise TF2ToolboxException("Could not retrieve the TF2 Item Schema.\n")


  # Load the schema into an usable form, with defindexes as keys.
  s = {}
  for entry in schema['result']['items']:
    used_by_classes = entry.get('used_by_classes', None)
    s[entry['defindex']] = {'name': entry['item_name'],
                            'slot': entry.get('item_slot', None),
                            'class': entry['item_class'],
                            'blacklist': is_blacklisted(entry),
                            'used_by': 'Multiple' if not used_by_classes or len(used_by_classes) > 1 else used_by_classes[0]
                            }

  # Load in schema quality mappings: 0 -> "Normal", 1 -> "Genuine"
  s['qualities'] = {}
  for quality in schema['result']['qualities']:
    # TODO: Hacky fix!!!! schema is fucking up hard.
    if quality not in schema['result']['qualityNames']:
      s['qualities'][schema['result']['qualities'][quality]] = quality.capitalize()
    else:
      s['qualities'][schema['result']['qualities'][quality]] = schema['result']['qualityNames'][quality]

  # Load in schema particle effects
  s['particles'] = {}
  for particle in schema['result']['attribute_controlled_attached_particles']:
    s['particles'][particle['id']] = particle['name']

  # Set up the result schema.
  result = {
    'CATEGORY_ORDER': [category for category in tf2toolbox.metadata.BPTEXT_FORM_CATEGORIES if category in form],
    'WEAPON_CATEGORIES': ['Strange Weapons', 'Genuine Weapons', 'Normal Weapons', 'Vintage Weapons']
  }
  for category in tf2toolbox.metadata.BPTEXT_FORM_CATEGORIES:
    result[category] = {}

  # Set up seen class sets for hats and weapons.
  if form['output_sort'] == 'class':
    seen_hat_classes = set()
    seen_weapon_classes = set()

  # Parse each item!
  # The sort_key variables are important for sorting the output.
  # Regular alphabetical sort key: ('Team Captain Untradeable Gifted Team Spirit', 150, 99)
  # (Item w/tags, craftnum, level)
  #
  # Class based sort key for HATS AND WEAPONS: (10, 'Team Captain Untradeable Gifted Pink', 150, 99)
  # The 10 represents Team Captain as a Multiple class item, hence sorting after Scout items (code 1),
  # Soldier items (code 2), etc.
  #
  # We also have special subcategory sort keys for class based sort.
  # The scout one looks like this: (1, 0, 'Scout')
  # This way, it gets sorted ahead of all the Scout weapons/hats (due to the 0).
  for item in bp['result']['items']:

    # Set item info from schema
    item['name'] = s[item['defindex']]['name']
    item['slot'] = s[item['defindex']]['slot']
    item['class'] = s[item['defindex']]['class']
    item['blacklist'] = s[item['defindex']]['blacklist']
    item['used_by'] = s[item['defindex']]['used_by']

    # Get item attributes
    item['attr'] = {}
    if 'attributes' in item:
      for attribute in item['attributes']:
        if attribute['defindex'] == 142:
          item['attr']['paint'] = int(attribute['float_value']) # 1.0 -> Team Spirit.
        elif attribute['defindex'] == 186:
          item['attr']['gifted'] = True
        elif attribute['defindex'] == 229 and (attribute['value'] <= 100 or 'display_craft_num' in form):
          item['attr']['craftnum'] = attribute['value']
        elif attribute['defindex'] == 134 and int(attribute['float_value']):
          item['attr']['particle'] = s['particles'][int(attribute['float_value'])]

    # Skip invalid items
    if should_skip(item, form):
      continue

    # Hats
    if item['slot'] in ['head', 'misc']:
      quality = s['qualities'][item['quality']]
      # Suffixes: Quality tag, Untradeable, Gifted, Painted, CraftNum, Level if specified

      # Get Unusual particle effect
      if quality == 'Unusual' and 'particle' in item['attr']:
        item['name'] = '%s (%s)' % (item['name'], item['attr']['particle'])

      sort_key = [item['name']]
      suffix_tags = []

      # Get craft num, level for sort key.
      craft_num = ''
      if 'attr' in item and 'craftnum' in item['attr']:
        sort_key.append(item['attr']['craftnum'])
        craft_num = ' #%d ' % item['attr']['craftnum']

      if 'display_hat_levels' in form:
        sort_key.append(item['level'])
        suffix_tags.append('Level %d' % item['level'])

      if 'flag_cannot_trade' in item:
        sort_key[0] += ' Untradeable'
        suffix_tags.append('Untradeable')

      if 'flag_cannot_craft' in item:
        sort_key[0] += ' Uncraftable'
        suffix_tags.append('Uncraftable')

      if 'gifted' in item['attr']:
        sort_key[0] += ' Gifted'
        suffix_tags.append('Gifted')

      # TODO: Fix dumb array copy hack to get suffix tags correct for plaintext vs bbcode.
      pt_suffix_tags = list(suffix_tags)
      if 'paint' in item['attr']:
        sort_key[0] += ' %s' % tf2toolbox.metadata.PAINT_NUMBER_MAP['plaintext'][item['attr']['paint']]
        suffix_tags.append(tf2toolbox.metadata.PAINT_NUMBER_MAP[item['attr']['paint']])
        pt_suffix_tags.append(tf2toolbox.metadata.PAINT_NUMBER_MAP['plaintext'][item['attr']['paint']])

      suffix = ' (%s)' % ', '.join(suffix_tags) if suffix_tags else ''
      pt_suffix = ' (%s)' % ', '.join(pt_suffix_tags) if pt_suffix_tags else ''

      plaintext_string = '%s%s%s%s' % (quality+' ' if quality != 'Unique' else '', item['name'], craft_num, pt_suffix)
      bbcode_string = '%s%s%s[/color]%s' % (tf2toolbox.metadata.QUALITY_BBCODE_MAP[quality], item['name'], craft_num, suffix)

      if item['name'] in tf2toolbox.metadata.RARE_PROMO_HATS:
        category = 'Rare Promos'
      elif quality != 'Genuine' and item['name'] in tf2toolbox.metadata.PROMO_HATS:
        category = 'Promo Hats'
      else:
        hat_quality = quality if quality in set(['Vintage', 'Genuine', 'Unusual']) else 'Normal'
        hat_quality = 'Unusual' if quality in set(['Community', 'Self-Made', 'Valve']) else hat_quality
        category = '%s Hats' % hat_quality

      # Depending on the output sort, add an extra value to the sort_key.
      if form['output_sort'] == 'class':
        sort_key.insert(0, tf2toolbox.metadata.TF2CLASS_SORT_DICT[item['used_by']])
        # If we haven't seen this class before, add it to seen classes.
        if (category, item['used_by']) not in seen_hat_classes:
          seen_hat_classes.add((category, item['used_by']))
          subcat_sort_key = (tf2toolbox.metadata.TF2CLASS_SORT_DICT[item['used_by']], 0, item['used_by'])
          result[category][subcat_sort_key] = {'SUBCATEGORY': item['used_by']}
      elif form['output_sort'] == 'release':
        pass

      sort_key = tuple(sort_key)
      add_to_result(result, sort_key, category, plaintext=plaintext_string, bbcode=bbcode_string)



    # Weapons
    elif item['slot'] in ['primary', 'secondary', 'melee', 'pda', 'pda2'] and item['class'] != 'slot_token':
      quality = s['qualities'][item['quality']]
      # Suffixes: Quality tag, Untradeable, Gifted, CraftNum, Weapon Level for Vintage
      # TODO: Support UHHH and other unusual weapons. Should probably go in Genuine Weapons.

      sort_key = [item['name']]
      suffix_tags = []

      # Get craft num, level for sort key.
      craft_num = ''
      if 'attr' in item and 'craftnum' in item['attr']:
        sort_key.append(item['attr']['craftnum'])
        craft_num = ' #%d ' % item['attr']['craftnum']

      if quality == 'Vintage' and (item['level'] not in tf2toolbox.metadata.WEAPON_LEVEL_MAP or item['name'] not in tf2toolbox.metadata.WEAPON_LEVEL_MAP[item['level']]):
        sort_key.append(item['level'])
        suffix_tags.append('Level %d' % item['level'])

      if 'flag_cannot_trade' in item:
        sort_key[0] += ' Untradeable'
        suffix_tags.append('Untradeable')

      if 'flag_cannot_craft' in item:
        sort_key[0] += ' Uncraftable'
        suffix_tags.append('Uncraftable')

      if 'gifted' in item['attr']:
        sort_key[0] += ' Gifted'
        suffix_tags.append('Gifted')

      suffix = ' (%s)' % ', '.join(suffix_tags) if suffix_tags else ''

      plaintext_string = '%s%s%s%s' % (quality+' ' if quality != 'Unique' else '', item['name'], craft_num, suffix)
      bbcode_string = '%s%s%s[/color]%s' % (tf2toolbox.metadata.QUALITY_BBCODE_MAP[quality], item['name'], craft_num, suffix)

      sort_quality = quality
      if quality == 'Unique':
        sort_quality = 'Normal'
      elif quality not in set(['Unique', 'Vintage', 'Strange']):
        sort_quality = 'Genuine'

      category = '%s Weapons' % sort_quality

      # Depending on the output sort, add an extra value to the sort_key.
      if form['output_sort'] == 'class':
        sort_key.insert(0, tf2toolbox.metadata.TF2CLASS_SORT_DICT[item['used_by']])
        # If we haven't seen this class before, add it to seen classes.
        if (category, item['used_by']) not in seen_weapon_classes:
          seen_weapon_classes.add((category, item['used_by']))
          subcat_sort_key = (tf2toolbox.metadata.TF2CLASS_SORT_DICT[item['used_by']], 0, item['used_by'])
          result[category][subcat_sort_key] = {'SUBCATEGORY': item['used_by']}
      elif form['output_sort'] == 'release':
        pass

      sort_key = tuple(sort_key)

      add_to_result(result, sort_key, category, plaintext=plaintext_string, bbcode=bbcode_string)

    # Paint
    elif item['name'] in tf2toolbox.metadata.PAINT_MAP:
      add_to_result(result, item['name'], 'Paint', bbcode=tf2toolbox.metadata.PAINT_MAP[item['name']]+'[/color]')

    # Tools
    elif item['class'] in ['tool', 'class_token', 'slot_token'] or item['slot'] == 'action':
      add_to_result(result, item['name'], 'Tools')

    # Metal
    elif item['class'] == 'craft_item':
      add_to_result(result, item['name'], 'Metal')

    # Crate
    elif item['class'] == 'supply_crate':
      if not 'attributes' in item or item['name'] != 'Mann Co. Supply Crate':
        add_to_result(result, item['name'], 'Crates')
      else:
        add_to_result(result, int(item['attributes'][0]['float_value']), 'Crates', plaintext="Series %d Crate" % int(item['attributes'][0]['float_value']))

  bptext_suffix_tags = []
  if 'display_sc_url' in form:
    bptext_suffix_tags.append('Steam Community URL: http://steamcommunity.com/%s' % ('id/'+session_info['customURL'] if 'customURL' in session_info else 'profiles/'+session_info['steamID']))

  if form['inc_bp_link'] != 'none':
    if form['inc_bp_link'] == 'tf2b':
      bptext_suffix_tags.append('TF2B: http://tf2b.com/tf2/%s' % (session_info['customURL'] if 'customURL' in session_info else session_info['steamID']))
    elif form['inc_bp_link'] == 'tf2items':
      bptext_suffix_tags.append('TF2Items: http://tf2items.com/%s' % ('id/'+session_info['customURL'] if 'customURL' in session_info else 'profiles/'+session_info['steamID']))
    elif form['inc_bp_link'] == 'optf2':
      bptext_suffix_tags.append('OPTF2: http://optf2.com/user/%s' % (session_info['customURL'] if 'customURL' in session_info else session_info['steamID']))

  # TODO: Fix icky hack here to append extra line for Reddit
  if form['output_type'] == 'markdown':
    bptext_suffix_tags = [tag+'\n' for tag in bptext_suffix_tags]

  return bp_to_text(result, 'display_credit' in form, 'only_dup_weps' in form, form['output_type']) + '\n' + '\n'.join(bptext_suffix_tags)

def add_to_result(result, sort_key, category, bbcode=None, plaintext=None, subcategory=None):
  """
  Given a result schema, a sort key (usually item_name. +bbcode/plaintext if needed),
  and its category (+subcategory if needed), add it to the result schema.

  bbcode is assigned to bbcode if possible, then to plaintext, then to sort_key.
  plaintext is assigned to plaintext if possible, then to sort_key.
  """
  if not bbcode:
    if plaintext:
      bbcode = plaintext
    else:
      bbcode = sort_key
  if sort_key not in result[category]:
    result[category][sort_key] = defaultdict(int)
  result[category][sort_key]['quantity'] += 1
  result[category][sort_key]['bbcode'] = bbcode
  if plaintext:
    result[category][sort_key]['plaintext'] = plaintext
  else:
    result[category][sort_key]['plaintext'] = sort_key



def is_blacklisted(entry):
  """
  Given an item entry from the TF2 Item Schema, determine if it is both
  untradeable and un-gift-wrappable. These items should never appear in the result.
  """
  if 'attributes' in entry:
    for attr in entry['attributes']:
      if attr['name'] == 'cannot trade':
        can_gift_wrap = False
        if 'capabilities' in entry:
          for cap in entry['capabilities']:
            if cap == 'can_gift_wrap' and entry['capabilities'][cap]:
              can_gift_wrap = True
              break
        if not can_gift_wrap:
          return True
          break
  return False

def should_skip(item, form):
  """
  Given a TF2 item JSON object, augmented with information from bp_parse(),
  and an user's form data options, returns True if we should skip parsing this item.
  """
  # Skip invalid pages
  if 'all' not in form.getlist('pages[]'):
    pages = [int(page) for page in form.getlist('pages[]')]
    inv_page = ((item['inventory'] & 0xFFFF) - 1) / 50 + 1
    if inv_page not in pages:
      return True

  # Skip item blacklist. TODO: Fix hack for Director's Vision
  if item['blacklist'] or item['name'] == "Taunt: The Director's Vision":
    return True

  # Skip untradeables if optioned.
  if 'hide_untradeable' in form and 'flag_cannot_trade' in item:
    return True

  if 'hide_uncraftable' in form and 'flag_cannot_craft' in item:
    return True

  # Skip gifted if optioned.
  if 'hide_gifted' in form and 'gifted' in item:
    return True

  return False

BPTEXT_LANGUAGE_SYNTAX = {
  'bbcode': {
    'category_opener': '[b][u]%s[/u][/b][list]',
    'category_opener_w_cred': '[b][u]%s[/u][/b][color=#cd5c5c] (List generated at [URL=http://tf2toolbox.com/bptext]TF2Toolbox.com[/URL])[/color][list]',
    'item_opener': '[*][b]',
    'item_closer': '[/b]',
    'category_closer': '[/list]\n',
    'subcat_exp': '[b]--- %s ---[/b]'
  },
  'plaintext': {
    'category_opener': '%s',
    'category_opener_w_cred': '%s (List generated at TF2Toolbox.com)',
    'item_opener': '',
    'item_closer': '',
    'category_closer': '',
    'subcat_exp': '--- %s ---'
  },
  'markdown': {
    'category_opener': '**%s**\n', # Extra new lines needed for Reddit.
    'category_opener_w_cred': '**%s**\n', # No credit at the top. Credit goes at bottom.
    'item_opener': '* ',
    'item_closer': '',
    'category_closer': '\n',
    'subcat_exp': '\n_%s_\n',
    'parse_text': 'plaintext',
    'bottom_credit': 'List generated at [TF2Toolbox.com](http://tf2toolbox.com/bptext) with help from [JonDum](http://www.reddit.com/r/tf2trade/comments/k2zru/tool_tf2toolboxcom_bbcode_converter/) at Reddit.\n'
  }
}

def bp_to_text(bp, credit, dup_weps_only, language):
  """
  Consolidation function that translates a parsed bp (from bp_parse()) to
  a specified language.
  """
  result_lines = []
  first = True
  for category in bp['CATEGORY_ORDER']:
    if not bp[category]:
      continue
    if credit and first:
      result_lines.append(BPTEXT_LANGUAGE_SYNTAX[language]['category_opener_w_cred'] % category)
      first = False
    else:
      result_lines.append(BPTEXT_LANGUAGE_SYNTAX[language]['category_opener'] % category)
    for sort_key in sorted(bp[category].keys()):
      # First check for subcategories. If not a subcategory, then we know its an item entry.
      if 'SUBCATEGORY' in bp[category][sort_key]:
        result_lines.append(BPTEXT_LANGUAGE_SYNTAX[language]['subcat_exp'] % bp[category][sort_key]['SUBCATEGORY'])
        continue
      if dup_weps_only and category in bp['WEAPON_CATEGORIES'] and bp[category][sort_key]['quantity'] == 1:
        continue
      text_type = BPTEXT_LANGUAGE_SYNTAX[language]['parse_text'] if 'parse_text' in BPTEXT_LANGUAGE_SYNTAX[language] else language
      quantity_string = ' x %d' % bp[category][sort_key]['quantity'] if bp[category][sort_key]['quantity'] > 1 else ''
      result_lines.append(BPTEXT_LANGUAGE_SYNTAX[language]['item_opener'] + bp[category][sort_key][text_type] + quantity_string + BPTEXT_LANGUAGE_SYNTAX[language]['item_closer'])
    result_lines.append(BPTEXT_LANGUAGE_SYNTAX[language]['category_closer'])

  if credit and 'bottom_credit' in BPTEXT_LANGUAGE_SYNTAX[language]:
    result_lines.append(BPTEXT_LANGUAGE_SYNTAX[language]['bottom_credit'])

  return '\n'.join(result_lines)


def bptext_form_to_params(form):
  """
  Given a form request from bptext, return a list of human-readable parameters
  for the user to read on the side of the result.

  The template will go through each element in the list and create a <li> bullet for each.
  """
  params_list = []

  # Items printing -- REMOVED since its redundant with the bptext output.
  #items_list = [opt.lower() for opt in tf2toolbox.metadata.BPTEXT_FORM_CATEGORIES if opt in form]
  #if not items_list:
  #  params_list.append('Displaying no items. Huh?')
  #else:
  #  params_list.append('Displaying %s.' % ', '.join(items_list))

  # Options printing
  if 'only_dup_weps' in form:
    params_list.append('Only showing duplicate weapons!')

  hidden_types = map(lambda x: x[1],
                     filter(lambda x: x[0] in form,
                           [('hide_untradeable', 'untradable'), ('hide_uncraftable', 'uncrafatble'), ('hide_gifted', 'gifted')])
                    )
  if len(hidden_types) > 0:
    params_list.append('Hiding %s items.' % '/'.join(hidden_types))

  # Print backpack pages displayed.
  page_list = form.getlist('pages[]')
  if 'all' in page_list:
    params_list.append('Displaying all backpack pages.')
  elif not page_list:
    params_list.append('Not displaying any backpack pages. Huh?')
  elif len(page_list) == 1:
    params_list.append('Displaying backpack page %s.' % str(page_list[0]))
  else:
    params_list.append('Displaying backpack pages %s.' % ', '.join([str(num) for num in page_list]))

  # Output sort.
  if form['output_sort'] == 'alpha':
    params_list.append('Sorting items alphabetically.')
  elif form['output_sort'] == 'class':
    params_list.append('Sorting items by TF2 class (Scout, Soldier, Pyro, etc.).')
  elif form['output_sort'] == 'release':
    params_list.append('Sorting items by TF2 update (Uber Update, etc.).')

  # Output type
  if form['output_type'] == 'bbcode':
    params_list.append('Translated backpack to BBCode.')
  elif form['output_type'] == 'markdown':
    params_list.append('Translated backpack to Reddit Markdown. Huge thanks to <a href="http://www.reddit.com/r/tf2trade/comments/k2zru/tool_tf2toolboxcom_bbcode_converter/">JonDum at Reddit</a> for the help and inspiration!')
  elif form['output_type'] == 'plaintext':
    params_list.append('Translated backpack to plaintext.')

  return params_list

