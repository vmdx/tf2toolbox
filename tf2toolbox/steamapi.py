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

if app.config['USE_MEMCACHED']:
    import memcache

def get_schema():
    """
    Returns the TF2 schema in JSON format.
    """
    schema_url = "http://api.steampowered.com/IEconItems_440/GetSchema/v0001/?key=%s&language=en" % app.config['STEAM_API_KEY']

    mtime = 0

    # Step 1. Check the schema cache - when was it last updated? (mtime / dt)
    if app.config['USE_MEMCACHED']:
        mc = memcache.Client([app.config['MEMCACHED_LOCATION']], debug=0)
        last_modified = mc.get('SCHEMA-CACHED-TIME')
        if not (last_modified is None):
            mtime = last_modified
    else:
        schema_cache = os.path.join(os.getcwd(), 'tf2toolbox/static/schema.json')
        if os.path.exists(schema_cache):
            mtime = os.path.getmtime(schema_cache)

    dt = datetime.datetime.utcfromtimestamp(mtime)
    print '[SCHEMA] Checking schema at mtime: %s' % dt.strftime('%a, %d %b %Y %X GMT')

    # Step 2. Make a request with If-Modified-Since = modified time.
    try:
        req = requests.get(schema_url, headers={'If-Modified-Since': dt.strftime('%a, %d %b %Y %X GMT')}, timeout=5)
        req.encoding = 'latin1'
    except (requests.exceptions.ConnectionError, requests.exceptions.Timeout):
        raise TF2ToolboxException("We were unable to retrieve the TF2 item schema. The SteamAPI may be down - please try again shortly.\n")

    # Step 3a. Pull from the cache if we get a 304 (content not modified)
    if req.status_code == 304:
        print '[IMPORTANT] Cached schema is up-to-date!'
        if app.config['USE_MEMCACHED']:
            schema_json = mc.get('SCHEMA')
            mc.disconnect_all()
            return schema_json
        else:
            schema = open(schema_cache)
            schema_json = json.load(schema, 'latin1')
            schema.close()
            return schema_json
    elif not req.ok:
        raise TF2ToolboxException("We were unable to retrieve the TF2 item schema. The SteamAPI may be down - please try again shortly.\n")

    # Step 3b. Otherwise, use the result of the HTTP request as the schema, AND cache it :)
    print '[SCHEMA] Retrieving new schema.'
    schema = str(req.content)
    schema_json = json.loads(schema, 'latin1')

    if app.config['USE_MEMCACHED']:
        mc.set('SCHEMA-CACHED-TIME', int(time.time()))
        mc.set('SCHEMA', schema_json)
        print '[SCHEMA] Wrote new schema cache to memcached.'
        mc.disconnect_all()
    else:
        new_schema_cache = open(schema_cache, 'w')
        new_schema_cache.write(schema)
        new_schema_cache.close()
        print '[SCHEMA] Wrote new schema cache to disk.'

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
        bp_json = json.loads(bp_text, 'latin1')    # Needs to be latin1 due to funky character names for gifted items.
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

    api_json = json.loads(str(req.content), 'latin1')    # Needs to be latin1 due to funky character names for usernames.
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
    if app.config['USE_MEMCACHED']:
        try:
            # no unicode strings in memcached! comes in as unicode.
            mc = memcache.Client([app.config['MEMCACHED_LOCATION']], debug=0)
            result = mc.get('VANITY-' + str(vanity_id))
            if not (result is None):
                print '[VANITY] Served lookup from memcached: %s -> %s' % (vanity_id, result)
                mc.disconnect_all()
                return result
        except UnicodeEncodeError:
            pass

    api_call = 'http://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/?vanityurl=%s&key=%s' % (vanity_id, app.config['STEAM_API_KEY'])

    try:
        req = requests.get(api_call, timeout=5)
        req.encoding = 'latin1'
    except (requests.exceptions.ConnectionError, requests.exceptions.Timeout):
        raise TF2ToolboxException("We were unable to retrieve that player's info. The SteamAPI may be down - please try again shortly.\n")

    if not req.ok:
        raise TF2ToolboxException("We were unable to retrieve that user's backpack. The URL may be wrong or the SteamAPI may be down.\n")

    api_json = json.loads(str(req.content), 'latin1')    # Needs to be latin1 due to funky character names for usernames.

    status = api_json['response']['success']
    if status == 1:
        if app.config['USE_MEMCACHED']:
            try:
                mc.set('VANITY-' + str(vanity_id), api_json['response']['steamid'])
                print '[VANITY] Cached lookup into memcached: %s -> %s' % (vanity_id, api_json['response']['steamid'])
                mc.disconnect_all()
            except UnicodeEncodeError:
                pass
        return api_json['response']['steamid']
    elif status == 42:
        raise TF2ToolboxException("Sorry, %s is not a valid SteamCommunity ID.\n" % vanity_id)
    else:
        raise TF2ToolboxException("There is a problem with Valve's API.")
    return None

def pretty(json_obj):
    print json.dumps(json_obj, sort_keys=True, indent=2)

