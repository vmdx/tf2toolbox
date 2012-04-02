from flask import Flask
app = Flask(__name__)

app.config.from_pyfile('config.py')
app.jinja_env.trim_blocks = True

import tf2toolbox.views

