"""
TF2Toolbox calls to the SteamAPI.
"""

import datetime, time
from flask import session
import os.path
import simplejson as json
import requests

from tf2toolbox import app
from tf2toolbox.exceptions import TF2ToolboxException

def get_schema():
  """
  Returns the TF2 schema in JSON format.
  """
  schema_cache = os.path.join(os.getcwd(), 'tf2toolbox/static/schema.json')
  schema_url = "http://api.steampowered.com/IEconItems_440/GetSchema/v0001/?key=%s&language=en" % app.config['STEAM_API_KEY']
  mtime = 0
  if os.path.exists(schema_cache):
    mtime = os.path.getmtime(schema_cache)

  dt = datetime.datetime.utcfromtimestamp(mtime)

  print '[SCHEMA] Checking schema at %s for mtime: %s' % (schema_cache, dt.strftime('%a, %d %b %Y %X GMT'))

  try:
    req = requests.get(schema_url, headers={'If-Modified-Since': dt.strftime('%a, %d %b %Y %X GMT')}, timeout=5)
    req.encoding = 'latin1'
  except (requests.exceptions.ConnectionError, requests.exceptions.Timeout):
    raise TF2ToolboxException("We were unable to retrieve the TF2 item schema. The SteamAPI may be down - please try again shortly.\n")

  if req.status_code == 304:
    print '[IMPORTANT] Cached schema is up-to-date!'
    schema = open(schema_cache)
    schema_json = json.load(schema, 'latin1')
    schema.close()
    return schema_json
  elif not req.ok:
    raise TF2ToolboxException("We were unable to retrieve the TF2 item schema. The SteamAPI may be down - please try again shortly.\n")
  else:
    schema = str(req.content)

  print '[SCHEMA] Retrieving new schema.'

  new_schema_cache = open(schema_cache, 'w')
  print '[SCHEMA] Writing new schema cache.'
  new_schema_cache.write(schema)
  new_schema_cache.close()

  schema_json = json.loads(schema, 'latin1')

  return schema_json

def get_user_backpack(steamid):
  """
  Given an user's Steam ID, returns the backpack as a JSON object.
  """
  backpack_url = "http://api.steampowered.com/IEconItems_440/GetPlayerItems/v0001/?SteamID=%s&key=%s" % (steamid, app.config['STEAM_API_KEY'])
  bp_text = None

  try:
    req = requests.get(backpack_url, timeout=5)
    req.encoding = 'latin1'
  except (requests.exceptions.ConnectionError, requests.exceptions.Timeout):
    raise TF2ToolboxException("We were unable to retrieve that user's backpack. The SteamAPI may be down - please try again shortly.\n")


  if not req.ok:
    raise TF2ToolboxException("We were unable to retrieve that user's backpack. The URL may be wrong or the SteamAPI may be down.\n")

  try:
    bp_text = str(req.content)
    bp_json = json.loads(bp_text, 'latin1')  # Needs to be latin1 due to funky character names for gifted items.
  except ValueError, e:
    # We need to find the offensive line of text and fix it.
    print "Caught malformed JSON, attempting to fix."
    if bp_text is None:
      return None
    bp_text = bp_text.replace('.\n', '.0\n')
    bp_text = bp_text.replace('\x04', '')
    bp_json = json.loads(bp_text, 'latin1')

  status = bp_json['result']['status']
  if status == 1: #backpack validation
    return bp_json
  elif status == 15:
    raise TF2ToolboxException("Sorry, this user's backpack is private.\n")
  elif status == 8 or status == 18:
    raise TF2ToolboxException("Invalid SteamID.\n")
  return None


def get_player_info(steamid):
  """
  Given a 64 bit Steam ID, set session variables.
  """
  api_call = 'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?steamids=%s&key=%s' % (steamid, app.config['STEAM_API_KEY'])

  try:
    req = requests.get(api_call, timeout=5)
    req.encoding = 'latin1'
  except (requests.exceptions.ConnectionError, requests.exceptions.Timeout):
    raise TF2ToolboxException("We were unable to retrieve that player's info. The SteamAPI may be down - please try again shortly.\n")


  if not req.ok:
    raise TF2ToolboxException("We were unable to retrieve that user's backpack. The URL may be wrong or the SteamAPI may be down.\n")

  api_json = json.loads(str(req.content), 'latin1')  # Needs to be latin1 due to funky character names for usernames.
  if 'response' not in api_json or 'players' not in api_json['response'] or not api_json['response']['players']:
    raise TF2ToolboxException("We were unable to retrieve info for that profile.\n")

  player_info = api_json['response']['players'][0]

  result = {}
  if 'personaname' in player_info:
    result['username'] = player_info['personaname']
  if 'steamid' in player_info:
    result['steamID'] = player_info['steamid']
  if 'avatarmedium' in player_info:
    result['avatar'] = player_info['avatarmedium']

  return result

def resolve_vanity_url(vanity_id):
  """
  Given a Steam Community vanity ID, get the 64 bit Steam ID.
  """
  api_call = 'http://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/?vanityurl=%s&key=%s' % (vanity_id, app.config['STEAM_API_KEY'])

  try:
    req = requests.get(api_call, timeout=5)
    req.encoding = 'latin1'
  except (requests.exceptions.ConnectionError, requests.exceptions.Timeout):
    raise TF2ToolboxException("We were unable to retrieve that player's info. The SteamAPI may be down - please try again shortly.\n")

  if not req.ok:
    raise TF2ToolboxException("We were unable to retrieve that user's backpack. The URL may be wrong or the SteamAPI may be down.\n")

  api_json = json.loads(str(req.content), 'latin1')  # Needs to be latin1 due to funky character names for usernames.

  status = api_json['response']['success']
  if status == 1:
    return api_json['response']['steamid']
  elif status == 42:
    raise TF2ToolboxException("Sorry, %s is not a valid SteamCommunity ID.\n" % vanity_id)
  else:
    raise TF2ToolboxException("There is a problem with Valve's API.")
  return None

def pretty(json_obj):
  print json.dumps(json_obj, sort_keys=True, indent=2)

