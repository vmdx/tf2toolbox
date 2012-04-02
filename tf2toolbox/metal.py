
from tf2toolbox.exceptions import TF2ToolboxException
from tf2toolbox.metadata import WEAPON_LEVEL_MAP
from tf2toolbox.steamapi import get_schema


def bp_metal(bp, form):
  """
  Given a JSON representation of a backpack, the user's request form,
  and a copy of the user's session info (steamID, customURL, etc.),
  returns a Python result dict to be rendered by the template.

  result = {
    'ordered_classes': ['Scout', 'Soldier', 'Pyro', 'Demoman', 'Heavy', 'Engineer', 'Medic', 'Sniper', 'Spy', 'Multiple'],
    'weapons': {},
    'v_all_count': 0,
    'nv_all_count': 0,
    'v_primary_count': 0,
    'nv_primary_count': 0,
    'v_secondary_count': 0,
    'nv_secondary_count': 0,
  }


  """
  schema = get_schema()
  if not schema:
    raise TF2ToolboxException("Could not retrieve the TF2 Item Schema.\n")

  # Load the schema into an usable form, with defindexes as keys.
  s = {}
  for entry in schema['result']['items']:
    s[entry['defindex']] = {'name': entry['item_name'],
                            'slot': entry.get('item_slot', None),
                            'class': entry['item_class'],
                            'craft': entry.get('craft_material_type', None),
                            'image': entry['image_url'],
                            'used_by': entry.get('used_by_classes', None)
                           }

  # Load in schema quality mappings: 0 -> "Normal", 1 -> "Genuine"
  s['qualities'] = {}
  for quality in schema['result']['qualities']:
    # TODO: Hacky fix!!!! schema is fucking up hard.
    if quality not in schema['result']['qualityNames']:
      s['qualities'][schema['result']['qualities'][quality]] = quality.capitalize()
    else:
      s['qualities'][schema['result']['qualities'][quality]] = schema['result']['qualityNames'][quality]


  # Set up the result schema.
  result = {
    'ordered_classes': ['Scout', 'Soldier', 'Pyro', 'Demoman', 'Heavy', 'Engineer', 'Medic', 'Sniper', 'Spy', 'Multiple'],
    'weapons': {},
    'v_all_count': 0,
    'nv_all_count': 0,
    'v_primary_count': 0,
    'nv_primary_count': 0,
    'v_secondary_count': 0,
    'nv_secondary_count': 0,
  }
  for cls in result['ordered_classes']:
    result['weapons'][cls] = {'standard': {}, 'special': [], 'total_s_count': 0, 'total_v_count': 0, 'total_nv_count': 0} # standard -> 'Item Name' {picture, nv_count, v_count}. special -> (picURL, altText)

  # Parse each item!
  for item in bp['result']['items']:

    # Skip invalid pages
    if 'all' not in form.getlist('pages[]'):
      pages = [int(page) for page in form.getlist('pages[]')]
      inv_page = ((item['inventory'] & 0xFFFF) - 1) / 50 + 1
      if inv_page not in pages:
        continue

    # Set item info from schema
    item['craft'] = s[item['defindex']]['craft']

    # If the item is a craftable weapon, count it!
    if item['craft'] == 'weapon':
      item['slot'] = s[item['defindex']]['slot']
      item['name'] = s[item['defindex']]['name']
      item['class'] = s[item['defindex']]['class']
      item['used_by'] = s[item['defindex']]['used_by']
      item['image'] = s[item['defindex']]['image']
      quality = s['qualities'][item['quality']]

      # Log item slot and count.
      if quality == 'Vintage':
        result['v_all_count'] += 1
      elif quality == 'Unique':
        result['nv_all_count'] += 1

      if item['slot'] in ['primary', 'secondary']:
        if quality == 'Vintage':
          result['v_' + item['slot'] + '_count'] += 1
        elif quality == 'Unique':
          result['nv_' + item['slot'] + '_count'] += 1

      # Figure out which TF2 class the item goes in
      if not item['used_by']:
        print '[WTF] Item not used by any class? %s' % item['name']
      elif len(item['used_by']) > 1:
        use_class = 'Multiple'
      elif len(item['used_by']) == 1:
        use_class = item['used_by'][0]

      # Classify the item.
      # Special weapons are any quality besides Unique/Vintage, or Vintage Offlevel, Named, or Described.
      if quality not in ['Unique', 'Vintage'] or (quality == 'Vintage' and (item['level'] not in WEAPON_LEVEL_MAP or item['name'] not in WEAPON_LEVEL_MAP[item['level']])) or 'custom_name' in item or 'custom_desc' in item:
        suffix_tags = []
        if 'custom_name' in item:
          display_name = item['custom_name']
          suffix_tags.append('%s%s' % (quality+' ' if quality != 'Unique' else '', item['name']))
        else:
          display_name = '%s%s' % (quality+' ' if quality != 'Unique' else '', item['name'])

        if quality == 'Vintage' and (item['level'] not in WEAPON_LEVEL_MAP or item['name'] not in WEAPON_LEVEL_MAP[item['level']]):
          suffix_tags.append('Level %d' % item['level'])

        if 'custom_desc' in item:
          suffix_tags.append('Description: %s' % item['custom_desc'])

        suffix = (' (%s)' % ', '.join(suffix_tags)) if suffix_tags else ''

        result['weapons'][use_class]['special'].append(('%s%s' % (display_name, suffix), item['image']))
        result['weapons'][use_class]['total_s_count'] += 1

      # Non special items just get entered.
      else:
        if item['name'] not in result['weapons'][use_class]['standard']:
          result['weapons'][use_class]['standard'][item['name']] = \
            {'image': item['image'],
             'v_count': 1 if quality == 'Vintage' else 0,
             'nv_count': 1 if quality == 'Unique' else 0
            }
        else:
          if quality == 'Vintage':
            result['weapons'][use_class]['standard'][item['name']]['v_count'] += 1
          elif quality == 'Unique':
            result['weapons'][use_class]['standard'][item['name']]['nv_count'] += 1
        if quality == 'Vintage':
          result['weapons'][use_class]['total_v_count'] += 1
        elif quality == 'Unique':
          result['weapons'][use_class]['total_nv_count'] += 1

  return result


def metal_form_to_params(form):
  """
  Given a form request from metal, return a list of human-readable parameters
  for the user to read on the side of the result.

  The template will go through each element in the list and create a <li> bullet for each.
  """
  params_list = []

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

  return params_list

