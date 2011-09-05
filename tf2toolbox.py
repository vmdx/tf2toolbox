"""
This is TF2Toolbox in Flask Python!
"""

from collections import defaultdict
import contextlib
import datetime
import httplib
import os
import simplejson as json
import urllib2
import xml.dom.minidom

import bpdata

from flask import *

# Configuration
DEBUG = False
SECRET_KEY = 'I\xa4RT\x9aH\xc6\xdbK\x13I\xdb\x18\xe1\xfd\x8d\xbf\xfa\x17\xa5E\x8f\xd2\xdd'

app = Flask(__name__)
app.config.from_object(__name__)
app.jinja_env.trim_blocks = True


@app.route('/', methods=['GET', 'POST'])
@app.route('/index', methods=['GET', 'POST'])
def index():
  template_info = {'nav_cell': 'Home'}
  if request.method == 'POST':
    set_user_session(template_info, request.form['steamURL'])
  return render_template('index.html', template_info=template_info, session=session)

@app.route('/about', methods=['GET', 'POST'])
def about():
  if request.method == 'POST':
    if request.form['form_id'] == 'signin':
      set_user_session(template_info, request.form['steamURL'])
  return render_template('about.html', template_info={}, session=session)

@app.route('/bptext', methods=['GET', 'POST'])
def bptext():
  template_info = {'nav_cell': 'Backpack Text', 'signin_action': '/bptext'}
  if request.method == 'POST':
    if request.form['form_id'] == 'signin':
      set_user_session(template_info, request.form['steamURL'])

    elif request.form['form_id'] == 'bptext':
      if 'steamID' in session:
        bp_json = get_user_backpack(template_info, session['steamID'])
        if 'error_msg' not in template_info:
          bp_parse(template_info, bp_json, request.form, session)
      return render_template('bptext_result.html', template_info=template_info, session=session)

  return render_template('bptext_form.html', template_info=template_info, session=session)

@app.route('/metal', methods=['GET', 'POST'])
def metal():
  template_info = {'nav_cell': 'Metal Maker', 'signin_action': '/metal'}
  if request.method == 'POST':
    if request.form['form_id'] == 'signin':
      set_user_session(template_info, request.form['steamURL'])

    elif request.form['form_id'] == 'metal':
      if 'steamID' in session:
        bp_json = get_user_backpack(template_info, session['steamID'])
        if 'error_msg' not in template_info:
          bp_metal(template_info, bp_json, request.form, session)
      return render_template('metal_result.html', template_info=template_info, session=session)

  return render_template('metal_form.html', template_info=template_info, session=session)

@app.route('/weapons', methods=['GET', 'POST'])
def weapons():
  template_info = {'nav_cell': 'Weapon Stock', 'signin_action': '/weapons'}
  if request.method == 'POST':
    if request.form['form_id'] == 'signin':
      set_user_session(template_info, request.form['steamURL'])
  bp_json = get_user_backpack(template_info, session['steamID'])
  if 'error_msg' not in template_info:
    bp_weapons(template_info, bp_json, session)
  return render_template('weapon_stock.html', template_info=template_info, session=session)

def set_user_session(template_info, steamURL):
  """
  Given a Steam Community URL, sets the following session variables for the current user:
    * username
    * avatar
    * steamID
    * num_bp_slots
    * customURL

  In the case of an error, adds an error message to template_info.

  TODO: Consider using xml.etree.ElementTree, or lxml, instead of xml.dom.minidom
  """
  session.pop('username', None)
  session.pop('avatar', None)
  session.pop('steamID', None)
  session.pop('num_bp_slots', None)
  session.pop('customURL', None)

  if not steamURL.startswith('http://steamcommunity.com/id/') and not steamURL.startswith('http://steamcommunity.com/profiles/'):
    template_info['error_msg'] = "That was not a valid Steam Community URL.\n"
    return

  steamURL += "?xml=1"

  try:
    url_file = urllib2.urlopen(steamURL)
    user_data = xml.dom.minidom.parse(url_file)
    root_children = user_data.documentElement.childNodes
    vars_set = 0
    for child in root_children:
      if child.nodeName ==  'steamID':
        session['username'] = child.firstChild.nodeValue
      elif child.nodeName == 'avatarMedium':
        session['avatar'] = child.firstChild.nodeValue
      elif child.nodeName == 'steamID64':
        session['steamID'] = child.firstChild.nodeValue
      elif child.nodeName == 'customURL':
        session['customURL'] = child.firstChild.nodeValue
  except urllib2.URLError:
    template_info['error_msg'] = "We were unable to retrieve that URL. Please try again!\n"
    return
  except xml.parsers.expat.ExpatError:
    pass

  if 'username' not in session or 'avatar' not in session or 'steamID' not in session:
    template_info['error_msg'] = "We were unable to retrieve info for that profile.\n"
    return

  bp_json = get_user_backpack(template_info, session['steamID'])
  if not bp_json:
    return
  session['num_bp_slots'] = bp_json['result']['num_backpack_slots']


def get_user_backpack(template_info, steamID):
  """
  Given an user's Steam ID, returns the backpack as a JSON object.

  In the case of an error, adds an error message to template_info.
  """
  backpack_url = "http://api.steampowered.com/IEconItems_440/GetPlayerItems/v0001/?SteamID=" + steamID + "&key=74EA34072E00ED29B92691B6F929F590"
  try:
    url_file = urllib2.urlopen(backpack_url)
    bp_json = json.load(url_file)
  except urllib2.URLError:
    template_info['error_msg'] = "We were unable to retrieve that user's backpack.\n"

  status = bp_json['result']['status']
  if status == 1: #backpack validation
    return bp_json
  elif status == 15:
    template_info['error_msg'] = "Sorry, this user's backpack is private.\n"
  elif status == 8 or status == 18:
    template_info['error_msg'] = "Invalid SteamID.\n"
  return None

def pretty(json_obj):
  print json.dumps(json_obj, sort_keys=True, indent=2)



def bp_weapons(template_info, bp, session_info):
  """
  Given a JSON representation of a backpack,  and a copy of the user's session info (steamID, customURL, etc.),
  returns the following in template_info.

    * Weapon info on the backpack
    * Errors if needed.

  """

  # Result schema;
  """
  'result': {
    'Scout': {
      'essential': {'Sandman': True, 'Soda Popper': False}
      'alternative': {'Three-Rune Blade': False}
    }
    'Soldier'...
    'Multiple'
  }
  """
  schema = get_schema()
  if not schema:
    template_info['error_msg'] = "Could not retrieve the TF2 Item Schema.\n"
    return

  result = {
    'ordered_classes': ['Scout', 'Soldier', 'Pyro', 'Demoman', 'Heavy', 'Engineer', 'Medic', 'Sniper', 'Spy', 'Multiple'],
    'Special': {'alternative': {}}
  }
  for cls in result['ordered_classes']:
    result[cls] = {'essential': {}, 'alternative': {}}

  # Load the schema into an usable form, with defindexes as keys.
  s = {}
  for entry in schema['result']['items']:
    # Load the weapon list.
    if entry.get('item_slot', None) in ['primary', 'secondary', 'melee', 'pda', 'pda2'] and entry['item_class'] != 'slot_token':
      used_by = entry.get('used_by_classes', None)
      item_name = entry['item_name']

      # Categorize the weapon. Is it essential (adds new functionality to game?) or alternative (replicates existing functionality/promotional)
      # Is it used by one class or multiple?
      # Is it a stock weapon? (defindex 0-30). If so, initialize to True.
      category = 'essential'
      if item_name in bpdata.ALT_WEAPONS or item_name in bpdata.LIMITED_WEAPONS:
        category = 'alternative'

      if not used_by or len(used_by) > 1: # Special case for Saxxy - used_by = None
        cls = 'Multiple'
      else:
        cls = used_by[0]

      if item_name not in result[cls][category]:
        if entry.get('defindex') <= 30:
          result[cls][category][item_name] = [True, entry.get('image_url')]
        else:
          result[cls][category][item_name] = [False, entry.get('image_url')]


      s[entry['defindex']] = {'name': entry['item_name'],
                              'used_by': cls,
                              'category': category
                             }

  # Parse each item!
  for item in bp['result']['items']:
    # Skip non-indexed weapons.
    if item['defindex'] not in s:
      continue

    cls = s[item['defindex']]['used_by']
    category = s[item['defindex']]['category']
    name = s[item['defindex']]['name']

    result[cls][category][name][0] = True

  template_info['result'] = result


def bp_metal(template_info, bp, form, session_info):
  """
  Given a JSON representation of a backpack, the user's request form,
  and a copy of the user's session info (steamID, customURL, etc.), returns the following in template_info.

    * Metal info on the backpack
    * Errors if needed.

  """
  schema = get_schema()
  if not schema:
    template_info['error_msg'] = "Could not retrieve the TF2 Item Schema.\n"
    return

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
      inv_page = (item['inventory'] & 0xFFFF - 1) / 50 + 1
      if inv_page not in form.getlist('pages[]'):
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
      if quality not in ['Unique', 'Vintage'] or (quality == 'Vintage' and (item['level'] not in bpdata.WEAPON_LEVEL_MAP or item['name'] not in bpdata.WEAPON_LEVEL_MAP[item['level']])) or 'custom_name' in item or 'custom_desc' in item:
        suffix_tags = []
        if 'custom_name' in item:
          display_name = item['custom_name']
          suffix_tags.append('%s%s' % (quality+' ' if quality != 'Unique' else '', item['name']))
        else:
          display_name = '%s%s' % (quality+' ' if quality != 'Unique' else '', item['name'])

        if quality == 'Vintage' and (item['level'] not in bpdata.WEAPON_LEVEL_MAP or item['name'] not in bpdata.WEAPON_LEVEL_MAP[item['level']]):
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


  template_info['result'] = result
  template_info['metal_params'] = metal_form_to_params(form)

  return


def bp_parse(template_info, bp, form, session_info):
  """
  Given a JSON representation of a backpack, the user's request form,
  and a copy of the user's session info (steamID, customURL, etc.), returns the following in template_info.

    * A BBCode representation of the backpack
    * A list of status on what parts of the backpack are being displayed
    * Errors if needed.

  """
  schema = get_schema()
  if not schema:
    template_info['error_msg'] = "Could not retrieve the TF2 Item Schema.\n"
    return

  # Load the schema into an usable form, with defindexes as keys.
  s = {}
  for entry in schema['result']['items']:
    s[entry['defindex']] = {'name': entry['item_name'],
                            'slot': entry.get('item_slot', None),
                            'class': entry['item_class'],
                            'blacklist': is_blacklisted(entry)
                           }

  # Load in schema quality mappings: 0 -> "Normal", 1 -> "Genuine"
  s['qualities'] = {}
  for quality in schema['result']['qualities']:
    s['qualities'][schema['result']['qualities'][quality]] = schema['result']['qualityNames'][quality]

  # Set up the result schema.
  result = {
    'CATEGORY_ORDER': [category for category in bpdata.BPTEXT_FORM_CATEGORIES if category in form],
  }
  for category in bpdata.BPTEXT_FORM_CATEGORIES:
    result[category] = {}

  # Parse each item!
  for item in bp['result']['items']:

    # Set item info from schema
    item['name'] = s[item['defindex']]['name']
    item['slot'] = s[item['defindex']]['slot']
    item['class'] = s[item['defindex']]['class']
    item['blacklist'] = s[item['defindex']]['blacklist']

    # Get item attributes
    item['attr'] = {}
    if 'attributes' in item:
      for attribute in item['attributes']:
        if attribute['defindex'] == 142:
          print item['name']
          item['attr']['paint'] = int(attribute['float_value']) # 1.0 -> Team Spirit.
          print 'Painted %d' % item['attr']['paint']
        elif attribute['defindex'] == 186:
          item['attr']['gifted'] = True
        elif attribute['defindex'] == 229 and (attribute['value'] <= 100 or 'display_craft_num' in form):
          item['attr']['craftnum'] = attribute['value']

    # Skip invalid items
    if should_skip(item, form):
      continue

    # Hats
    if item['slot'] in ['head', 'misc']:
      quality = s['qualities'][item['quality']]
      # Suffixes: Quality tag, Untradeable, Gifted, Painted, CraftNum, Level if specified

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

      if 'gifted' in item['attr']:
        sort_key[0] += ' Gifted'
        suffix_tags.append('Gifted')

      # TODO: Fix dumb array copy hack to get suffix tags correct for plaintext vs bbcode.
      pt_suffix_tags = list(suffix_tags)
      if 'paint' in item['attr']:
        sort_key += ' %s' % bpdata.PAINT_NUMBER_MAP['plaintext'][item['attr']['paint']]
        suffix_tags.append(bpdata.PAINT_NUMBER_MAP[item['attr']['paint']])
        pt_suffix_tags.append(bpdata.PAINT_NUMBER_MAP['plaintext'][item['attr']['paint']])

      suffix = ' (%s)' % ', '.join(suffix_tags) if suffix_tags else ''
      pt_suffix = ' (%s)' % ', '.join(pt_suffix_tags) if pt_suffix_tags else ''
      sort_key = tuple(sort_key)

      plaintext_string = '%s%s%s%s' % (quality+' ' if quality != 'Unique' else '', item['name'], craft_num, pt_suffix)
      bbcode_string = '%s%s%s[/color]%s' % (bpdata.QUALITY_BBCODE_MAP[quality], item['name'], craft_num, suffix)

      if item['name'] in bpdata.RARE_PROMO_HATS:
        category = 'Rare Promos'
      elif quality != 'Genuine' and item['name'] in bpdata.PROMO_HATS:
        category = 'Promo Hats'
      else:
        category = '%s Hats' % (quality if quality != 'Unique' else 'Normal')

      add_to_result(result, sort_key, category, plaintext=plaintext_string, bbcode=bbcode_string)



    # Weapons
    elif item['slot'] in ['primary', 'secondary', 'melee', 'pda', 'pda2']:
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

      if quality == 'Vintage' and (item['level'] not in bpdata.WEAPON_LEVEL_MAP or item['name'] not in bpdata.WEAPON_LEVEL_MAP[item['level']]):
        sort_key.append(item['level'])
        suffix_tags.append('Level %d' % item['level'])

      if 'flag_cannot_trade' in item:
        suffix_tags.append('Untradeable')

      if 'gifted' in item['attr']:
        suffix_tags.append('Gifted')

      suffix = ' (%s)' % ', '.join(suffix_tags) if suffix_tags else ''
      sort_key = tuple(sort_key)

      plaintext_string = '%s%s%s%s' % (quality+' ' if quality != 'Unique' else '', item['name'], craft_num, suffix)
      bbcode_string = '%s%s%s[/color]%s' % (bpdata.QUALITY_BBCODE_MAP[quality], item['name'], craft_num, suffix)

      sort_quality = quality
      if quality == 'Unique':
        sort_quality = 'Normal'
      elif quality == 'Unusual':
        sort_quality = 'Genuine'

      add_to_result(result, sort_key, '%s Weapons' % sort_quality, plaintext=plaintext_string, bbcode=bbcode_string)

    # Paint
    elif item['name'] in bpdata.PAINT_MAP:
      add_to_result(result, item['name'], 'Paint', bbcode=bpdata.PAINT_MAP[item['name']]+'[/color]')

    # Tools
    elif item['class'] in ['tool', 'class_token', 'slot_token'] or item['slot'] == 'action':
      add_to_result(result, item['name'], 'Tools')

    # Metal
    elif item['class'] == 'craft_item':
      add_to_result(result, item['name'], 'Metal')

    # Crate
    elif item['class'] == 'supply_crate':
      add_to_result(result, int(item['attributes'][0]['float_value']), 'Crates', plaintext="Series %d Crate" % int(item['attributes'][0]['float_value']))

  bptext_suffix_tags = []
  if 'display_sc_url' in form:
    bptext_suffix_tags.append('SteamCommunity URL: http://steamcommunity.com/%s' % ('id/'+session['customURL'] if 'customURL' in session else 'profiles/'+session['steamID']))

  if form['inc_bp_link'] != 'none':
    if form['inc_bp_link'] == 'tf2b':
      bptext_suffix_tags.append('TF2B: http://tf2b.com/%s' % (session['customURL'] if 'customURL' in session else session['steamID']))
    elif form['inc_bp_link'] == 'tf2items':
      bptext_suffix_tags.append('TF2Items: http://tf2items.com/%s' % ('id/'+session['customURL'] if 'customURL' in session else 'profiles/'+session['steamID']))
    elif form['inc_bp_link'] == 'optf2':
      bptext_suffix_tags.append('OPTF2: http://optf2.com/user/%s' % (session['customURL'] if 'customURL' in session else session['steamID']))


  if form['output_type'] == 'bbcode':
    template_info['bptext_result_string'] = bp_to_bbcode(result) + '\n'.join(bptext_suffix_tags)
  elif form['output_type'] == 'plaintext':
    template_info['bptext_result_string'] = bp_to_plaintext(result) + '\n'.join(bptext_suffix_tags)
  template_info['bptext_params'] = bptext_form_to_params(form)

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

def get_schema():
  """
  Returns the TF2 schema in JSON format.

  TODO: Optimization - Cache it locally on the web server, HTTP request with If-Modified-Since. If yes,
  writeover the cached copy.

  Two issues: where to store? in memcache or as a file?
              how to pull? http request every time with if-modified-since, or time based (expiration 1 hour)

  curl -v -H "If-Modified-Since: Wed, 24 Aug 2011 11:08:08 GMT" http://api.steampowered.com/IEconItems_440/GetSchema/v0001/?key=74EA34072E00ED29B92691B6F929F590&language=en

  Look into Python requests library.
  """
  schema_cache = os.path.join(os.getcwd(), 'static/schema.json')
  schema_url = "http://api.steampowered.com/IEconItems_440/GetSchema/v0001/?key=74EA34072E00ED29B92691B6F929F590&language=en"
  mtime = 0
  if os.path.exists(schema_cache):
    mtime = os.path.getmtime(schema_cache)

  dt = datetime.datetime.utcfromtimestamp(mtime)

  schema_req = urllib2.Request(schema_url)
  schema_req.add_header('If-Modified-Since', dt.strftime('%a, %d %b %Y %X GMT'))

  print '[SCHEMA] Checking schema for mtime: %s' % dt.strftime('%a, %d %b %Y %X GMT')

  try:
    schema = urllib2.urlopen(schema_req)
    print '[SCHEMA] Retrieving new schema.'
    schema_string = schema.read()

    new_schema_cache = open(schema_cache, 'w')
    print '[SCHEMA] Writing new schema cache.'
    new_schema_cache.write(schema_string)
    new_schema_cache.close()

    schema_json = json.loads(schema_string)

  except urllib2.HTTPError as e:
    print e
    print e.code
    if e.code == 304:
      print '[IMPORTANT] Cached schema is up-to-date!'
      schema = open(schema_cache)
      schema_json = json.load(schema)
      schema.close()
    else:
      return None

  return schema_json


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
    inv_page = (item['inventory'] & 0xFFFF - 1) / 50 + 1
    if inv_page not in form.getlist('pages[]'):
      return True

  # Skip item blacklist. TODO: Fix hack for Director's Vision
  if item['blacklist'] or item['name'] == "Taunt: The Director's Vision":
    return True

  # Skip untradeables if optioned.
  if 'hide_untradeable' in form and 'flag_cannot_trade' in item:
    return True

  # Skip gifted if optioned.
  if 'hide_gifted' in form and 'gifted' in item:
    return True

  return False

def bp_to_bbcode(bp):
  """
  Given a parsed bp (from bp_parse()), translate it to BBCode. Return the string.
  """
  result = ""

  for category in bp['CATEGORY_ORDER']:
    result += '[b][u]%s[/u][/b][list]\n' % category
    for item in sorted(bp[category].keys()):
      result += '[*][b]' + bp[category][item]['bbcode']
      if bp[category][item]['quantity'] > 1:
        result += ' x %d' % bp[category][item]['quantity']
      result += '[/b]\n'
    if not bp[category]:
      result += 'None\n'
    result += '[/list]\n\n'

  return result

def bp_to_plaintext(bp):
  """
  Given a parsed bp (from bp_parse()), translate it to plaintext. Return the string.
  """
  result = ""

  for category in bp['CATEGORY_ORDER']:
    result += '%s\n' % category
    for item in sorted(bp[category].keys()):
      result += bp[category][item]['plaintext']
      if bp[category][item]['quantity'] > 1:
        result += ' x %d' % bp[category][item]['quantity']
      result += '\n'
    if not bp[category]:
      result += 'None\n'
    result += '\n'

  return result

def bptext_form_to_params(form):
  """
  Given a form request from bptext, return a list of human-readable parameters
  for the user to read on the side of the result.

  The template will go through each element in the list and create a <li> bullet for each.
  """
  params_list = []

  # Items printing
  items_list = [opt.lower() for opt in bpdata.BPTEXT_FORM_CATEGORIES if opt in form]
  if not items_list:
    params_list.append('Displaying no items. Huh?')
  else:
    params_list.append('Displaying %s.' % ', '.join(items_list))

  # Options printing
  if 'hide_untradeable' in form and 'hide_gifted' in form:
    params_list.append('Hiding untradeable and gifted items.')
  elif 'hide_gifted' in form:
    params_list.append('Hiding gifted items.')
  elif 'hide_untradeable' in form:
    params_list.append('Hiding untradeable items.')

  # Print backpack pages displayed.
  page_list = form.getlist('pages[]')
  if 'all' in page_list:
    params_list.append('Displaying all backpack pages.')
  elif not page_list:
    params_list.append('Not displaying any backpack pages. Huh?')
  elif len(page_list) == 1:
    params_list.append('Displaying backpack page %d.' % page_list[0])
  else:
    params_list.append('Displaying backpack pages %s.' % ', '.join([str(num) for num in page_list]))

  return params_list

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
    params_list.append('Displaying backpack page %d.' % page_list[0])
  else:
    params_list.append('Displaying backpack pages %s.' % ', '.join([str(num) for num in page_list]))

  return params_list


if __name__ == '__main__':
  app.run()
