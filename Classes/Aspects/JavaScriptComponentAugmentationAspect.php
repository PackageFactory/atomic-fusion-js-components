<?php
namespace PackageFactory\AtomicFusion\JsComponents\Aspects;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Aop\JoinPointInterface;
use Neos\Flow\Reflection\ClassReflection;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Neos\Service\HtmlAugmenter;

/**
 * @Flow\Scope("singleton")
 * @Flow\Aspect
 */
class JavaScriptComponentAugmentationAspect
{
    /**
     * @Flow\Inject
     * @var HtmlAugmenter
     */
    protected $htmlAugmenter;

    /**
     * @Flow\InjectConfiguration(path="tryFiles")
     * @var array
     */
    protected $tryFiles;

    /**
     * @Flow\Around("setting(PackageFactory.AtomicFusion.JsComponents.enable) && method(PackageFactory\AtomicFusion\FusionObjects\ComponentImplementation->evaluate())")
     * @Flow\Around("setting(PackageFactory.AtomicFusion.JsComponents.enable) && method(Neos\Fusion\FusionObjects\ComponentImplementation->evaluate())")
     * @param JoinPointInterface $joinPoint
     * @return string
     */
    public function augmentComponentWithComponentInformation(JoinPointInterface $joinPoint) : string
    {
        $componentImplementation = $joinPoint->getProxy();
        $fusionPrototypeName = $this->getFusionObjectNameFromFusionObject($componentImplementation);
        $renderedComponent = $joinPoint->getAdviceChain()->proceed($joinPoint);

        if ($javascriptFileName = $this->getJavaScriptFileNameFromFusionPrototypeName($fusionPrototypeName)) {
            return $this->htmlAugmenter->addAttributes(
                $renderedComponent,
                ['data-component' => $fusionPrototypeName]
            );
        }

        return $renderedComponent;
    }

    public function getJavaScriptFileNameFromFusionPrototypeName(string $fusionPrototypeName) : string
    {
        list($packageKey, $componentName) = explode(':', $fusionPrototypeName);
        $fusionPrototypeNameSegments = explode('.', $componentName);
        $componentPath = implode('/', $fusionPrototypeNameSegments);
        $componentBaseName = array_pop($fusionPrototypeNameSegments);

        foreach ($this->tryFiles as $fileNamePattern) {
            $fileName = $fileNamePattern;
            $fileName = str_replace('{fusionPrototypeName}', $fusionPrototypeName, $fileName);
            $fileName = str_replace('{packageKey}', $packageKey, $fileName);
            $fileName = str_replace('{componentPath}', $componentPath, $fileName);
            $fileName = str_replace('{componentBaseName}', $componentBaseName, $fileName);

            if (file_exists($fileName)) {
                return $fileName;
            }
        }

        return '';
    }

    public function getFusionObjectNameFromFusionObject(AbstractFusionObject $fusionObject) : string
    {
        $fusionObjectReflection = new ClassReflection($fusionObject);
        $fusionObjectName = $fusionObjectReflection->getProperty('fusionObjectName')->getValue($fusionObject);

        return $fusionObjectName;
    }
}
