from flask import render_template, request, session
import time

from tf2toolbox import app
from tf2toolbox.exceptions import TF2ToolboxException
from tf2toolbox.steamapi import get_user_backpack
from tf2toolbox.utils import set_user_session, send_notification_email

import tf2toolbox.bptext
import tf2toolbox.metal
import tf2toolbox.weapons

def allow_signin(func):
    def wrapped():
        try:
            if request.method == 'POST' and request.form['form_id'] == 'signin':
                request.form['steamURL'].decode('ascii')
                set_user_session(request.form['steamURL'])
        except TF2ToolboxException as e:
            return func(error=str(e))
        except UnicodeEncodeError as e:
            return func(error='That Steam Community URL contains invalid characters')
        return func()

    # app.route() relies on the original function name, so we need to make sure to set it
    wrapped.__name__ = func.__name__
    return wrapped


@app.route('/', methods=['GET', 'POST'])
@app.route('/index', methods=['GET', 'POST'])
@app.route('/index.html', methods=['GET', 'POST'])
@allow_signin
def index(error=''):
    template_info = {'nav_cell': 'Home', 'error_msg': error}
    return render_template('index.html', template_info=template_info, session=session)

@app.route('/about', methods=['GET', 'POST'])
@allow_signin
def about(error=''):
    return render_template('about.html', template_info={'error': error}, session=session)

@app.route('/donate', methods=['GET', 'POST'])
@allow_signin
def donate(error=''):
    return render_template('donate.html', template_info={'error': error}, session=session)


@app.route('/bptext', methods=['GET', 'POST'])
@allow_signin
def bptext(error=''):
    template_info = {'nav_cell': 'Backpack Text', 'signin_action': '/bptext'}
    if error:
        template_info['error_msg'] = error
    if request.method == 'POST' and request.form['form_id'] == 'bptext' and 'steamID' in session:
        try:
            bp_json = get_user_backpack(session['steamID'])
            template_info['bptext_result_string'] = tf2toolbox.bptext.bp_parse(bp_json, request.form, session)
            template_info['bptext_params'] = tf2toolbox.bptext.bptext_form_to_params(request.form)
        except TF2ToolboxException as e:
            template_info['error_msg'] = str(e)
        return render_template('bptext_result.html', template_info=template_info, session=session)

    return render_template('bptext_form.html', template_info=template_info, session=session)

@app.route('/metal', methods=['GET', 'POST'])
@allow_signin
def metal(error=''):
    template_info = {'nav_cell': 'Metal Maker', 'signin_action': '/metal', 'error_msg': error}

    if not error and request.method == 'POST' and request.form['form_id'] == 'metal' and 'steamID' in session:
        try:
            bp_json = get_user_backpack(session['steamID'])
            template_info['result'] = tf2toolbox.metal.bp_metal(bp_json, request.form)
            template_info['metal_params'] = tf2toolbox.metal.metal_form_to_params(request.form)
        except TF2ToolboxException as e:
            template_info['error_msg'] = str(e)
        return render_template('metal_result.html', template_info=template_info, session=session)

    return render_template('metal_form.html', template_info=template_info, session=session)


@app.route('/weapons', methods=['GET', 'POST'])
@allow_signin
def weapons(error=''):
    template_info = {'nav_cell': 'Weapon Stock', 'signin_action': '/weapons', 'error_msg': error}
    try:
        if not error and 'steamID' in session:
            bp_json = get_user_backpack(session['steamID'])
            template_info['result'] = tf2toolbox.weapons.bp_weapons(bp_json, session)
    except TF2ToolboxException as e:
        template_info['error_msg'] = str(e)

    return render_template('weapon_stock.html', template_info=template_info, session=session)


@app.errorhandler(500)
def internal_server_error(e):
    # Create the error message.
    error_msg = {'error': str(e), 'request': str(request), 'form': str(request.form), 'session': str(session)}

    if True and not app.debug:
        send_notification_email('TF2Toolbox ERROR: %s' % time.ctime(time.time()), str(error_msg))
        print '[500 ERROR] Error email successfully sent!'

    return render_template('500.html', error_info=error_msg)

