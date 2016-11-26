An example output produced by the `LoggableContainerBuilder`.

```
    "NamespaceExaminer": {
        "prototype": 3,
        "prototype-backtrace": [
            [
                "require",
                "require_once",
                "call_user_func",
                "{closure}",
                "SemanticCite::onExtensionFunction",
                "SCI\\HookRegistry::__construct",
                "SCI\\HookRegistry::addCallbackHandlers",
                "SMW\\ApplicationFactory::getNamespaceExaminer",
                "Onoi\\CallbackContainer\\LoggableContainerBuilder::create"
            ],
            [
                "OutputPage::addParserOutput",
                "OutputPage::addParserOutputMetadata",
                "Hooks::run",
                "call_user_func_array",
                "SMW\\MediaWiki\\Hooks\\HookRegistry::SMW\\MediaWiki\\Hooks\\{closure}",
                "SMW\\MediaWiki\\Hooks\\OutputPageParserOutput::process",
                "SMW\\MediaWiki\\Hooks\\OutputPageParserOutput::canPerformUpdate",
                "SMW\\MediaWiki\\Hooks\\OutputPageParserOutput::isSemanticEnabledNamespace",
                "SMW\\ApplicationFactory::getNamespaceExaminer",
                "Onoi\\CallbackContainer\\LoggableContainerBuilder::create"
            ],
            [
                "SkinTemplate::outputPage",
                "SkinTemplate::prepareQuickTemplate",
                "Hooks::run",
                "call_user_func_array",
                "SBL\\HookRegistry::SBL\\{closure}",
                "SBL\\SkinTemplateOutputModifier::modifyOutput",
                "SBL\\SkinTemplateOutputModifier::canModifyOutput",
                "SBL\\SkinTemplateOutputModifier::isEnabled",
                "SMW\\ApplicationFactory::getNamespaceExaminer",
                "Onoi\\CallbackContainer\\LoggableContainerBuilder::create"
            ]
        ],
        "singleton": 0,
        "singleton-memory": [],
        "singleton-time": [],
        "prototype-memory-median": 1432,
        "prototype-time-median": 0.00018199284871419
    },
```