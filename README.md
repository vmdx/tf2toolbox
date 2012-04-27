## TF2Toolbox

Backpack parsing utilities for Team Fortress 2.

#### Quickstart

Fill out a 'config.py' file in the `tf2toolbox` source directory - the 'config.py.example' file will guide you.

Get the requirements.

    pip install -r requirements.txt

Start the server (from the root directory)!

    python runserver.py

#### Info

The live instance runs on tf2toolbox.com.

Thanks for stopping by!

### Use of Memcached

TF2Toolbox can optionally use `memcached` to store SteamAPI results (vanity URL lookups, profile information, etc.). This
behavior can be set in `tf2toolbox/config.py`. Use of `memcached` requires memcached to be running locally.

To install on Mac OS X:
[](https://wincent.com/wiki/Installing_memcached_1.4.1_on_Mac_OS_X_10.6_Snow_Leopard)
brew install libevent

To install on Linux:
http://code.google.com/p/memcached/wiki/NewInstallFromPackage

Memcached keys and values:
  'vmdx' -> 
  (steam vanity name) -> (64 bit steam id)
