from tf2toolbox.metadata import ALT_WEAPONS, LIMITED_WEAPONS
from tf2toolbox.steamapi import get_schema

def bp_weapons(bp, session_info):
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
    raise TF2ToolboxException("Could not retrieve the TF2 Item Schema.\n")

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

      # Skip multiclass stock upgradeables.
      if entry.get('defindex') >= 190 and entry.get('defindex') <= 212:
        continue
      used_by = entry.get('used_by_classes', None)
      item_name = entry['item_name']

      # Categorize the weapon. Is it essential (adds new functionality to game?) or alternative (replicates existing functionality/promotional)
      # Is it used by one class or multiple?
      # Is it a stock weapon? (defindex 0-30). If so, initialize to True.
      category = 'essential'
      if item_name in ALT_WEAPONS or item_name in LIMITED_WEAPONS:
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

  return result
