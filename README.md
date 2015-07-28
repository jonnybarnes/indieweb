#IndieWeb helpers
These tools should help with things like POSSE-ing. Currently I'm only working
with Twitter but adding other services is in the pipeline.

##Installing
Super simple with [composer](https://getcomposer.org):

```bash
composer require jonnybarnes/indieweb
```

##Numbers
The `Numbers` class allows you to convert between decimal and either NewBase60
or NewBase64, and vice versa.

```php
$numbers = new Numbers();
$nb60id = $numbers->b60tunum($realId);
```

##NotePrep
The `NotePrep` class is for use when preparing a note that you want to
[POSSE](https://indiewebcamp.com/POSSE) to another site, such as Twitter. With a
provided note the `createNote` method will add a link to the original copy,
truncating the note if necessary.

```php
$noteprep = new NotePrep();
$originalNote = 'A lovely note.';
$posseCopy = $noteprep->createNote($note, 'https://abc.de/n/id', 140, true);
echo $posseCopy;// 'A lovely note. (https://abc.de/n/id)'
```

##License
As a library intended for use in the [IndieWeb](https://indiewebcamp.com) this
is Licensed under the
[Creative Commons Zero](https://spdx.org/licenses/CC0-1.0.html) license.
