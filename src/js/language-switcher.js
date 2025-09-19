// Language data object
const languageData = {
    en: {
        title: "Super Admin User Guide",
        templateManagement: "1. Template Management",
        newTemplate: "Create New Template:",
        newTemplateSteps: [
            "Go to Template List page",
            "Click on \"Create New Template\" button",
            "Enter template name and description",
            "Design the template in content editor",
            "Click Save button"
        ],
        editTemplate: "Edit Template:",
        editTemplateSteps: [
            "Select template from template list",
            "Click Edit button",
            "Make necessary changes",
            "Click Save button"
        ],
        userManagement: "2. User Management",
        newUser: "Create New User:",
        newUserSteps: [
            "Go to Admin List page",
            "Click on \"Add New Admin\" button",
            "Fill in user information",
            "Set roles and permissions",
            "Click Save button"
        ],
        managePermissions: "Manage User Permissions:",
        permissionSteps: [
            "Select user from admin list",
            "Click Edit button",
            "Go to permissions section",
            "Update necessary permissions",
            "Click Save button"
        ],
        contentManagement: "3. Content Management",
        updatePage: "Update Page Content:",
        updatePageSteps: [
            "Go to Content Management section",
            "Select page to edit",
            "Make changes in content editor",
            "Check preview",
            "Click Publish button"
        ],
        importantTips: "4. Important Tips",
        securityTip: "Security Tip: Always use strong passwords and change them regularly.",
        backupTip: "Backup Tip: Take backups before making important changes.",
        permissionTip: "Permission Tip: Give users only the permissions they actually need.",
        helpSupport: "5. Help and Support",
        contactInfo: "For any issues or assistance, please contact:",
        technicalSupport: "Technical Support: support@example.com",
        helpDesk: "Help Desk: 1800-XXX-XXXX"
    },
    hi: {
        title: "सुपर एडमिन यूजर गाइड",
        templateManagement: "1. टेम्पलेट मैनेजमेंट",
        newTemplate: "नया टेम्पलेट बनाना:",
        newTemplateSteps: [
            "टेम्पलेट लिस्ट पेज पर जाएं",
            "\"Create New Template\" बटन पर क्लिक करें",
            "टेम्पलेट का नाम और विवरण दर्ज करें",
            "कंटेंट एडिटर में टेम्पलेट का डिज़ाइन करें",
            "Save बटन पर क्लिक करें"
        ],
        editTemplate: "टेम्पलेट एडिट करना:",
        editTemplateSteps: [
            "टेम्पलेट लिस्ट में से टेम्पलेट चुनें",
            "Edit बटन पर क्लिक करें",
            "आवश्यक बदलाव करें",
            "Save बटन पर क्लिक करें"
        ],
        userManagement: "2. यूजर मैनेजमेंट",
        newUser: "नया यूजर बनाना:",
        newUserSteps: [
            "एडमिन लिस्ट पेज पर जाएं",
            "\"Add New Admin\" बटन पर क्लिक करें",
            "यूजर की जानकारी भरें",
            "रोल और परमिशन सेट करें",
            "Save बटन पर क्लिक करें"
        ],
        managePermissions: "यूजर परमिशन मैनेज करना:",
        permissionSteps: [
            "एडमिन लिस्ट में से यूजर चुनें",
            "Edit बटन पर क्लिक करें",
            "परमिशन सेक्शन में जाएं",
            "आवश्यक परमिशन अपडेट करें",
            "Save बटन पर क्लिक करें"
        ],
        contentManagement: "3. कंटेंट मैनेजमेंट",
        updatePage: "पेज कंटेंट अपडेट करना:",
        updatePageSteps: [
            "कंटेंट मैनेजमेंट सेक्शन में जाएं",
            "एडिट करने के लिए पेज चुनें",
            "कंटेंट एडिटर में बदलाव करें",
            "प्रीव्यू चेक करें",
            "Publish बटन पर क्लिक करें"
        ],
        importantTips: "4. महत्वपूर्ण टिप्स",
        securityTip: "सुरक्षा टिप: हमेशा मजबूत पासवर्ड का उपयोग करें और नियमित रूप से पासवर्ड बदलें।",
        backupTip: "बैकअप टिप: महत्वपूर्ण बदलाव करने से पहले बैकअप लें।",
        permissionTip: "परमिशन टिप: यूजर्स को केवल वही परमिशन दें जो उन्हें वास्तव में चाहिए।",
        helpSupport: "5. सहायता और समर्थन",
        contactInfo: "किसी भी समस्या या सहायता के लिए, कृपया संपर्क करें:",
        technicalSupport: "टेक्निकल सपोर्ट: support@example.com",
        helpDesk: "हेल्प डेस्क: 1800-XXX-XXXX"
    }
};

// Function to switch language
function switchLanguage(lang) {
    const currentLang = languageData[lang];
    if (!currentLang) return;

    // Update all text content
    document.title = currentLang.title;
    document.querySelector('h1').textContent = currentLang.title;

    // Update template management section
    document.querySelector('#template-management').textContent = currentLang.templateManagement;
    document.querySelector('#new-template').textContent = currentLang.newTemplate;
    updateList('#new-template-steps', currentLang.newTemplateSteps);
    document.querySelector('#edit-template').textContent = currentLang.editTemplate;
    updateList('#edit-template-steps', currentLang.editTemplateSteps);

    // Update user management section
    document.querySelector('#user-management').textContent = currentLang.userManagement;
    document.querySelector('#new-user').textContent = currentLang.newUser;
    updateList('#new-user-steps', currentLang.newUserSteps);
    document.querySelector('#manage-permissions').textContent = currentLang.managePermissions;
    updateList('#permission-steps', currentLang.permissionSteps);

    // Update content management section
    document.querySelector('#content-management').textContent = currentLang.contentManagement;
    document.querySelector('#update-page').textContent = currentLang.updatePage;
    updateList('#update-page-steps', currentLang.updatePageSteps);

    // Update tips section
    document.querySelector('#important-tips').textContent = currentLang.importantTips;
    document.querySelector('#security-tip').textContent = currentLang.securityTip;
    document.querySelector('#backup-tip').textContent = currentLang.backupTip;
    document.querySelector('#permission-tip').textContent = currentLang.permissionTip;

    // Update help and support section
    document.querySelector('#help-support').textContent = currentLang.helpSupport;
    document.querySelector('#contact-info').textContent = currentLang.contactInfo;
    document.querySelector('#technical-support').textContent = currentLang.technicalSupport;
    document.querySelector('#help-desk').textContent = currentLang.helpDesk;

    // Update language selector
    document.querySelector('#language-selector').value = lang;
}

// Helper function to update list items
function updateList(selector, items) {
    const list = document.querySelector(selector);
    if (!list) return;

    while (list.firstChild) {
        list.removeChild(list.firstChild);
    }

    items.forEach(item => {
        const li = document.createElement('li');
        li.textContent = item;
        list.appendChild(li);
    });
}

// Initialize language based on HTML lang attribute
document.addEventListener('DOMContentLoaded', () => {
    const currentLang = document.documentElement.lang || 'en';
    switchLanguage(currentLang);
});