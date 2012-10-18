from flask import session

from tf2toolbox.exceptions import TF2ToolboxException
from tf2toolbox.steamapi import resolve_vanity_url, get_player_info, get_user_backpack

def set_user_session(steamURL):
    """
    Given a Steam Community URL, sets the following session variables for the current user:
        * username
        * avatar
        * steamID
        * num_bp_slots
        * customURL

    In the case of an error, raises an exception.
    """
    session.pop('username', None)
    session.pop('avatar', None)
    session.pop('steamID', None)
    session.pop('num_bp_slots', None)
    session.pop('customURL', None)

    if not steamURL.startswith('http://steamcommunity.com/id/') and not steamURL.startswith('http://steamcommunity.com/profiles/'):
        raise TF2ToolboxException("That was not a valid Steam Community URL.\n")

    try:
        if steamURL.startswith('http://steamcommunity.com/id/'):
            steamID64 = resolve_vanity_url(steamURL[len('http://steamcommunity.com/id/'):])
            if steamID64 is None:
                return
            session['customURL'] = steamURL[len('http://steamcommunity.com/id/'):]
        else:
            steamID64 = steamURL[len('http://steamcommunity.com/profiles/'):]

        for key, value in get_player_info(steamID64).iteritems():
            session[key] = value
    except UnicodeEncodeError, e:
        raise TF2ToolboxException('Your Steam Community URL contained an invalid character.')

    if 'username' not in session or 'avatar' not in session or 'steamID' not in session:
        raise TF2ToolboxException("We were unable to retrieve info for that profile.\n")

    bp_json = get_user_backpack(session['steamID'])
    if not bp_json:
        return
    session['num_bp_slots'] = bp_json['result']['num_backpack_slots']


def send_notification_email(subject, message):
    # Read the email info file and parse
    msg = MIMEText(message)
    msg['Subject'] = subject
    msg['To'] = app.config['EMAIL_RECEIVER']
    # Send the email via Gmail.
    s = smtplib.SMTP(app.config['SMTP_SERVER'])
    s.ehlo()
    s.starttls()
    s.ehlo()
    s.login(app.config['EMAIL_SENDER'], app.config['EMAIL_SENDER_PASSWORD'])
    s.sendmail(app.config['EMAIL_SENDER'], app.config['EMAIL_RECEIVER'], msg.as_string())
    s.quit()

