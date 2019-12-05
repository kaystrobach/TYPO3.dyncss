<a href="https://enjoy.gitstore.app/repositories/kaystrobach/TYPO3.dyncss"><img src="https://enjoy.gitstore.app/repositories/badge-kaystrobach/TYPO3.dyncss.svg"></a>

Less in general
===============

The parser libs may ship dedicated information based on the language you want to use.

Frontend
========

Example TYPOScript:

	page.includeCSS.testLess = EXT:dyncss_test/Resources/Public/Stylesheets/Example.less

Example Overrides (dynamic color settings, dynamic image overrides):

```typoscript
plugin.tx_dyncss {
	register = LOAD_REGISTER
	register {
		inputColor1.cObject = TEXT
		inputColor1.cObject {
			value = {$lessColorScheme}
			split {
				token.char = 124
				returnKey = 0
			}
		}
		inputColor2 < .inputColor1
		inputColor2.cObject.split.returnKey = 1
	}
	overrides {
		inputColor1 = TEXT
		inputColor1 {
			data = register:inputColor1
		}
		inputColor2 = TEXT
		inputColor2 {
			data = register:inputColor2
		}
		logo = IMG_RESOURCE
		logo {
			stdWrap.wrap = url("|")
			file = GIFBUILDER
			file {
				XY = [20.w],[20.h]
				20 = IMAGE
				20.file = GIFBUILDER
				20.file {
					XY = 128,22
					backColor.data = register:inputColor1
				}
				20.mask = EXT:example/css/colors/less/images/logo_sw.png
			}
		}
	}
	registerReset = RESTORE_REGISTER
}
```

Example less file:

```less
	@linkColor: blue;
	@logo: url(someWeirdUrl);

	a {
		color: @linkColor;
	}

	h1 {
		a {
			color: lighten(@linkColor, 20%);
		}
	}
	#logo {
		background-image:@logo
	}
```

Backend: Include in backend.php
===============================

To see how it works, please take a look into dyncss_test.

Caching
=======

In production mode, CSS is only re-rendered if the topmost less or sass file, which is directly included by typoscript, is altered.  
In development mode, also changes in files that are imported inside a less or sass file trigger a new rendering.  
Development mode is triggered either by TYPO3 application context "Development" or by the preset "Development" in the install tool.
Additionally rerendering will happen if you change TS values, which are used in the less files.

Additionally we add a new clear cache button to just clear the dyncss cache.

![dyncss clear cache](Documentation/Images/clearcache.png)

Source maps
==========

If the compiling library supports source maps, you can enable that feature by enabling the debug mode of dyncss in the extensionmanager settings.

Normally the source maps are appended to the generated css files, to avoid confusion.

__IMPORTANT__

You need to disable css concatenation to get the sourcemaps working.

This can be done with the following TS based on your environment:
```
themes.configuration.css.concatenate = 0
page.config.concatenateCss = 0
```

![sourcemap](Documentation/Images/sourcemap.png)
