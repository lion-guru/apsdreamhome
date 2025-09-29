# नया फोल्डर स्ट्रक्चर

इस फोल्डर में सभी कॉमन फाइल्स और कॉन्फिगरेशन फाइल्स को व्यवस्थित किया गया है:

## फोल्डर स्ट्रक्चर

```
includes/
├── config/           # सभी कॉन्फिगरेशन फाइल्स
│   ├── config.php    # मुख्य कॉन्फिगरेशन फाइल
│   └── db_config.php # डेटाबेस कॉन्फिगरेशन
│
├── functions/        # सभी फंक्शन फाइल्स
│   └── common-functions.php # कॉमन फंक्शंस
│
└── templates/        # सभी टेम्पलेट फाइल्स
    ├── header.php    # कॉमन हेडर
    └── footer.php    # कॉमन फुटर
```

## फाइल्स का विवरण

### कॉन्फिगरेशन फाइल्स
- `config.php`: मुख्य कॉन्फिगरेशन फाइल जो सभी बेसिक सेटिंग्स को परिभाषित करती है
- `db_config.php`: डेटाबेस कनेक्शन और कॉन्फिगरेशन

### फंक्शन फाइल्स
- `common-functions.php`: सभी कॉमन फंक्शंस जो पूरे प्रोजेक्ट में इस्तेमाल होते हैं

### टेम्पलेट फाइल्स
- `header.php`: सभी पेजों के लिए कॉमन हेडर
- `footer.php`: सभी पेजों के लिए कॉमन फुटर

## इस्तेमाल

फाइल्स को इस तरह इंक्लूड करें:

```php
// कॉन्फिगरेशन
include("includes/config/config.php");

// कॉमन फंक्शंस
include("includes/functions/common-functions.php");

// टेम्पलेट्स
include("includes/templates/header.php");
include("includes/templates/footer.php");
```