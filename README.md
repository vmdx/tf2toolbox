## TF2Toolbox

[![Build Status](https://secure.travis-ci.org/vmdx/tf2toolbox.png?branch=master)](http://travis-ci.org/vmdx/tf2toolbox)

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

TF2Toolbox can optionally use `memcached` to store SteamAPI results (vanity URL lookups, the schema!). This
behavior can be set in `tf2toolbox/config.py`. Use of `memcached` requires memcached to be running locally.

To install on Mac OS X: [link](https://wincent.com/wiki/Installing_memcached_1.4.1_on_Mac_OS_X_10.6_Snow_Leopard)
    
To install on Linux: http://code.google.com/p/memcached/wiki/NewInstallFromPackage , or `apt-get install memcached`

Memcached keys and values:

* **SCHEMA**: the schema in a Python dict
* **SCHEMA-CACHED-TIME**: the last time the schema was cached
* **VANITY-(steam vanity name)**: -> (64 bit steam id)

### Memcached and Large Values (i.e. the TF2 Schema)

**10/17/2012**

After a week of fighting with inexplicable nginx errors being returned to a growing subset
of users on use of any of TF2Toolbox's tools, I spent a night tracking down the root cause.

Note that no change in TF2Toolbox's code had happened when these errors surfaced.

The root cause was that TF2's item schema, provided by Valve, exceeded 1 megabyte in size,
most likely due to an update that released new items into the TF2 world.

By default, memcached, which we use to cache a number of things in TF2Toolbox, including
the item schema, has a maximum value size of, yes, 1 megabyte.

memcached does indeed allow for configuration for values of over 1 megabyte to be stored,
at the expense of increasing the slab page size and decreasing performance. But despite
reconfiguring memcached to allow for larger values, I was still receiving null responses
for the cached schema.

The python-memcached library, which has long gone out of development, has a built-in
variable for maximum memcached server value size. The library had gone out of development
before memcached supported boosting the maximum value size, so I wasn't too bummed
to find that variable. But I was saddened by this little snippet of code.

    #  silently do not store if value length exceeds maximum
    if self.server_max_value_length != 0 and \
        len(val) > self.server_max_value_length: return(0)
 
The silent failure certainly added an hour or so to the chase down the rabbit hole, but
thankfully, the max value length variable is indeed configurable on python-memcached
clients, so we can continue using the library for storing the schema.

Still, this does bring up questions on whether memcached is even the right solution to
storing the TF2 item schema. Does it make sense to penalize the performance of vanity 
lookup keys, which are comparatively _teeny_, by bumping up the slab page size just to
accomodate the item schema in memcached? Perhaps serving the item schema in its own
memcached server is the right path. Or what about Redis, another key-value store primarily
served in-memory? These are things I will consider as TF2Toolbox development continues :)
