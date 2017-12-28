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
     * @Flow\Around("setting(PackageFactory.AtomicFusion.JsComponents.enable) && method(PackageFactory\AtomicFusion\FusionObjects\ComponentImplementation->evaluate())")
     * @Flow\Around("setting(PackageFactory.AtomicFusion.JsComponents.enable) && method(Neos\Fusion\FusionObjects\ComponentImplementation->evaluate())")
     * @param JoinPointInterface $joinPoint
     * @return mixed
     */
    public function augmentComponentWithComponentInformation(JoinPointInterface $joinPoint)
    {
        $componentImplementation = $joinPoint->getProxy();
        $fusionObjectName = $this->getFusionObjectNameFromFusionObject($componentImplementation);
        $packageName = $this->getPackageNameFromFusionObject($componentImplementation);
        $renderedComponent = $joinPoint->getAdviceChain()->proceed($joinPoint);

        list($packageName, $componentName) = explode(':', $fusionObjectName);
        $componentNameSegements = explode('.', $componentName);
        $componentPath = implode('/', $componentNameSegements);
        $trailingComponentNameSegment = array_pop($componentNameSegements);

        $javascriptFileCandidates = [
            sprintf('resource://%s/Private/Fusion/%s/%s.js', $packageName, $componentPath, $trailingComponentNameSegment),
            sprintf('resource://%s/Private/Fusion/%s/index.js', $packageName, $componentPath),
            sprintf('resource://%s/Private/Fusion/%s/Index.js', $packageName, $componentPath),
            sprintf('resource://%s/Private/Fusion/%s/component.js', $packageName, $componentPath),
            sprintf('resource://%s/Private/Fusion/%s/Component.js', $packageName, $componentPath),
            sprintf('resource://%s/Private/Fusion/%s.js', $packageName, $componentPath)
        ];

        $javascriptFileName = null;
        foreach ($javascriptFileCandidates as $javascriptFileCandidate) {
            if (file_exists($javascriptFileCandidate)) {
                $javascriptFileName = $javascriptFileCandidate;
                break;
            }
        }

        if ($javascriptFileName) {
            return $this->htmlAugmenter->addAttributes(
                $renderedComponent,
                ['data-component' => $fusionObjectName]
            );
        }

        return $renderedComponent;
    }

    public function getFusionObjectNameFromFusionObject(AbstractFusionObject $fusionObject)
    {
        $fusionObjectReflection = new ClassReflection($fusionObject);
        $fusionObjectName = $fusionObjectReflection->getProperty('fusionObjectName')->getValue($fusionObject);

        return $fusionObjectName;
    }

    /**
     * Get the package name for a given fusion object
     *
     * @param AbstractFusionObject $fusionObject
     * @return string
     */
    public function getPackageNameFromFusionObject(AbstractFusionObject $fusionObject)
    {
        $fusionObjectName = $this->getFusionObjectNameFromFusionObject($fusionObject);

        list($packageName) = explode(':', $fusionObjectName);

        return $packageName;
    }
}
