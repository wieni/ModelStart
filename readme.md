# Model Start

This is a simple script to create controllers, models, and twig files. It is a "Drush Script"
and should be placed into the "scripts" folder of your project and not in a public place.

## How to install

Copy the modelStart.php file to the projects scripts directory.

```
cp modelStart.php ~~project~~/web/scripts
```

## When to run

After you have created all the nodes, taxnonomy vocabularies, and eck entities.

## How to run

Goto the projects public dir.

```
cd project/web/public
```

Then run:
```
../scripts/modelStart.php
```

## What is this going to do

The script will create:

  - controllers for both node bundles and taxonomy vocabularies
  - entity/models for node bundles, taxonomy vocabularies, and eck bundles.
  - tempalte twig files for node bundles, taxonomy vocabularies, and eck bundles.

## Will this overwrite my existing files?

For each file that will be created a check will be done to see if the file already exists.

If it does then you will be prompted for what to do.

  - If you answer Yes (1) then the file will be overwitten.
  - If you answer option (2), the content of what it would have written is display in screen.
  - If you hit enter/cancel it will go on to the next file.

## Hold the phone, my X bundle didn't get created...
Go into the script and be sure the the array variables have what you need.
```
// We are going to make models and twigs for these types and bundles.
$models = [];
$models[] = 'node';
$models[] = 'taxonomy_term';
$models[] = 'paragraph';
$models[] = 'item';
$models[] = 'subcontent';

// We are going to make controllers for these types and bundles.
$controllers = [];
$controllers[] = 'node';
$controllers[] = 'taxonomy_term';
```


