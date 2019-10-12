- preview e-mails from an order 
or customer view in the Magento administration.

- export the preview to PDF (wkhtmltopdf is required, set its path in Store Configuration -> General -> Email Preview)

If you'd like to disable this functionality for non-developers only, use a DI configuration like this for now:
```xml
    <type name="JohnRogar\MageEmailPreview\Block\Adminhtml\EmailTemplates">
            <arguments>
                <argument name="checkDeveloperMode" xsi:type="boolean">
                    true
                </argument>
            </arguments>
        </type>
```

Future release will have more options and be configurable with ACL.