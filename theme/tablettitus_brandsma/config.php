<?php
$THEME->name = 'tablettitus_brandsma';

$THEME->parents = array(
    'canvas',
    'base',
);


$THEME->sheets = array(
    'core',
    'pagelayout',
	'blocks',
);

$THEME->parents_exclude_sheets = array(
        'base'=>array(
            'pagelayout',
		),
        'canvas'=>array(
            'pagelayout',
		),
);

$THEME->enable_dock = true;
