# Model Start
This is a simple script to create controllers, models, and twig files. It is a "Drush Script"
and should be placed into the "scripts" folder of your project and not in a public place.

## How to install
### IN YOUR VAGRANT
Copy the modelStart.php file to the projects scripts directory.
```
cp modelStart.php ~~project~~/web/scripts
```
Then make sure that the script is executable.
```
chmod a+x ~~project~~/web/scripts/modelStart.php
```

## When to run
**AFTER/POST/NA** the creation of all the nodes, fields, taxonomies, vocabularies, and eck entities.

## How to run
Goto the projects public dir.
```
cd ~~project~~/web/public
```
Then run:
```
../scripts/modelStart.php
```

## What is this going to do?
The script will create:

  - controllers for both node bundles and taxonomy vocabularies
  - entity/models for node bundles, taxonomy vocabularies, and eck bundles.
    - The models will have getter functions for all the fields in the bundle.
  - template twig files for node bundles, taxonomy vocabularies, and eck bundles.
    - The twig files will basic html with calls to all of the getters.

## Will this overwrite my existing files?
On each file that is to be created a check will be done to see if the file already exists.

If the file already exists then you will be prompted for what to do.

  - Option 0 [Cancel/Enter], skip go to the next file.
  - Option 1 Yes, you will overwrite the file.
  - Option 2 You will not overwrite the file, instead you will display the content on the screen

## Hold the phone, my X bundle didn't get created...
You can tweak the script, the interesting variables are at the top.

Go into the script and be sure the the array variables have what you need.
```
// STUFF YOU NEED TO WORRY ABOUT //

// Models, we will make models for everything in here.
$models = [];
$models[] = 'node';
$models[] = 'taxonomy_term';
$models[] = 'paragraph';
$models[] = 'item';
$models[] = 'subcontent';

// We are going to make controllers for these types.
$controllers = [];
$controllers[] = 'node';
$controllers[] = 'taxonomy_term';

// We are going to make teasers for these types.
$teasers = [];
$teasers[] = 'node';
$teasers[] = 'taxonomy_term';

// END STUFF YOU NEED TO WORRY ABOUT //
```


