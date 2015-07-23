#IndieWeb helpers
These tools should help with things like POSSE-ing. Currently I'm only working
with Twitter but adding other services is in the pipeline.

##Installing
As this is still in early development I haven’t added this to packagist yet.
However this is still easy to install with the magic that is composer. First we
need to add a repositories section to our `composer.json` file in order to
manually tell composer about this GitHub repo:

```json
"repositories": [
    {
        "name": "jonnybarnes/indieweb",
        "type": "git",
        "url": "https://github.com/jonnybarnes/indieweb.git"
    }
]
```

Then it’s simple a case of adding a dependency as normal:

```json
"require": {
    "jonnybanres/indieweb": "dev-master"
}
```
