Documentation
-------------

API documentation and installation instructions can be found at [read the docs][1].
Documentation source is hosted at [github][2]. Any documentation-related issues should
go there.

Contributing
------------

Fork the [repository][5] on github and make a pull request on the `development` branch.

Stable branches
---------------

The `master` branch is considered stable, although we always try to keep `development`
stable too. To be on the safe side use `master` or any of the available
[release tags][3].

Dependencies
------------

* php >= 5.3.x
* MySQL/MariaDB >= 5.1.x

Required php modules are:

* gd
* ldap
* mysql-pdo
* openssl
* soap

See the [installation instructions][7] for more info.

Plugins
-------

The project requires the [Twitter Bootstrap CakePHP Helper][6]. It resides in the
`plugins` folder as a git submodule.

After first cloning the repositority run:

    $ git submodule init
    $ git submodule update


To keep any sub-modules up-to-date run:

    $ git submodule foreach git pull

API
---

See the [API documentation][4].

License
-------

See `LICENSE`.


[1]: https://offers.readthedocs.org/en/latest/
[2]: https://github.com/teiath/offers-docs
[3]: https://github.com/teiath/offers/tags
[4]: https://offers.readthedocs.org/en/latest/api.html
[5]: https://github.com/teiath/offers/
[6]: https://github.com/loadsys/twitter-bootstrap-helper
[7]: https://offers.readthedocs.org/en/latest/installation.html
