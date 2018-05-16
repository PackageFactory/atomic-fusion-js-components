# PackageFactory.AtomicFusion.JsComponents

> Augment Fusion components to ease initialization with JavaScript

## Why?

TBD.

## Usage

TBD.

## Example Runtime

TBD.

## Discovery of JavaScript files

Fusion actually holds no information about the file a prototype is defined in. So, to enable the discovery of files put alongside those component \*.fusion files, we need to configure a lookup pattern:

```yaml
PackageFactory:
  AtomicFusion:
    JsComponents:
      tryFiles:
        - resource://{packageKey}/Private/Fusion/{componentPath}.js
```

With the above configuration the JS file for the prototype `prototype(Vendor.Site:MyAwesomeComponent)` will be assumed as `resource://Vendor.Site/Private/Fusion/MyAwesomeComponent.js`.

`{packageKey}` and `{componentPath}` are variables that will be replaced with runtime information of the respective fusion prototype.

The following variables are considered:

* `{prototypeName}` - The entire prototype name
* `{packageKey}` - The part of the prototype name before the `:`
* `{componentName}` - The part of the prototype name after the `:`
* `{componentBaseName}` - The last part of the componentName, if seperated by dots (for `Vendor.Site:Atom.Button` that would be: `Button`)
* `{componentPath}` - Similar to `{componentName}` with all dots being replaced by Directory Separators.

By default, the package looks at the following patterns:

```
resource://{packageKey}/Private/Fusion/{componentPath}.js
resource://{packageKey}/Private/Fusion/{componentPath}/{componentBaseName}.js
resource://{packageKey}/Private/Fusion/{componentPath}/index.js
resource://{packageKey}/Private/Fusion/{componentPath}/Index.js
resource://{packageKey}/Private/Fusion/{componentPath}/component.js
resource://{packageKey}/Private/Fusion/{componentPath}/Component.js
```

## Caveats

Recently, the native component prototype `Neos.Fusion:Component` has arrived in the Neos.Fusion core. Unfortunately, this package won't work with this new prototype and relies on `PackageFactory.AtomicFusion:Component` to be present.

This will likely change in the future, either through this package or a PR to Neos.Fusion.

If you still want to use this package with an existing code base, that relies on `Neos.Fusion:Component`, you could replace the `Neos.Fusion:Component` standard implementation with the one for `PackageFactory.AtomicFusion:Component`:

```
prototype(Neos.Fusion:Component) {
    @class = 'PackageFactory\\AtomicFusion\\FusionObjects\\ComponentImplementation'
}
```

`PackageFactory.AtomicFusion:Component` is fully compatible to `Neos.Fusion:Component`, but you should be nonetheless aware:

*THIS IS NOT THE JEDI WAY!*

## License

see [LICENSE.md](./LICENSE.md)
