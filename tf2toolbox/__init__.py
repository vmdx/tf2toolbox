from flask import Flask
import os.path
app = Flask(__name__)

# if config.py does not exist, use config.py.example.
if not os.path.exists('config.py'):
    print '[WARNING] Using config.py.example!'
    app.config.from_pyfile('config.py.example')
else:
    app.config.from_pyfile('config.py')

app.jinja_env.trim_blocks = True

import tf2toolbox.views

