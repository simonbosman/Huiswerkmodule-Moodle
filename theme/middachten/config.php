<?php
$THEME->name = 'middachten';

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
