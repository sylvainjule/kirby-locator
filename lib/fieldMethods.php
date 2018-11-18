<?php

return array(
	'toLocation' => function($field) {
		$structure = new Structure([$field->yaml()], $field->parent());
	    return $structure->first();
	},
);