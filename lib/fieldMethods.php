<?php

return array(
	'toLocation' => function($field) {
		if(version_compare(Kirby::version(), '4.0.0', '>=')) {
            $structure = Structure::factory([$field->yaml()], ['parent' => $field->parent()]);
        }
        else {
            $structure = new Structure([$field->yaml()], $field->parent());
        }

	    return $structure->first();
	},
);