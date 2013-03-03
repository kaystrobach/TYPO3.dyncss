Less in general
=====

You can find the documentation of less css on http://lesscss.org
This project uses http://leafo.net/lessphp/ to parse the lessfile and store it as css file

Frontend
=====

Example TYPOScript:

	page.includeCSS.testLess = EXT:less/Resources/Private/Less/Example.less
	page.includeCSS.testLess.overrides {
		linkColor = green!important
	}

	plugin.tx_less {
		overrides {
			linkColor = TEXT
			linkColor.value = blue
		}
	}

Example less file:
	@linkColor: blue;

	a {
		color: @linkColor;
	}

	h1 {
		a {
			color: lighten(@linkColor, 20%);
		}
	}


Backend: Include in backend.php
=====

Example for ext_tables.php

	$overrides = t3lib_div::makeInstance('tx_Less_Configuration_BeRegistry');
	$overrides->set('color', 'blue');
	$GLOBALS['TYPO3_CONF_VARS']['typo3/backend.php']['additionalBackendItems'][] = t3lib_extMgm::extPath($_EXTKEY).'Classes/BE/Hook.php';

Content of the Hook.php

	<?php
		$TYPO3backend->addCssFile('less_test', t3lib_extMgm::extRelPath('less_test') . 'Resources/Public/Stylesheets/test_be.less');
	?>

Backend: Include in template.php
=====

Example of ext_tables.php

	$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/template.php']['preHeaderRenderHook'] = ...

Content of the Classfile:

	Just make a class and add a function, you will get an array of params with a reference to the pagerenderer.
	Just add the less file with addCssFile


The lessfile may contain any valid less content.


Old Style (now unsupported)
=====
plugin.tx_less_pi1 {
	# define single less-files and css destination file
	lessFiles {
		1 {
#			less = fileadmin/templates/css/screen.less
		}
	}

	# point to the directory of less-Files
	lessDir = fileadmin/templates/

	# Search recursive for less-Files in "lessDir"
	recursivityLevel = 99

	# Show debug informations
	debug = {$plugin.tx_less_pi1.debug}

	# enable less compiler
	# if website is in production set this to 0,
	# otherwise less compile the css on every pageload
	enable = {$plugin.tx_less_pi1.enable}
}

# Example to insert less compiler in your page
# page.77 < plugin.tx_less_pi1
