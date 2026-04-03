# Gemini Code Assist Fix Guide - APS Dream Home

## 🚨 **Problem Identified**
```
Error: The set project ID (shaped-water-p1xb4) was invalid, or the current account lacks permission to view it.
```

## 🔧 **Solution Steps**

### **Step 1: Get Valid Google Cloud Project ID**

1. **Open Google Cloud Console**: https://console.cloud.google.com/
2. **Select/Create Project**: 
   - Click project dropdown (top left)
   - Create new project or select existing one
3. **Enable Gemini API**:
   - Go to "APIs & Services" → "Library"
   - Search "Gemini API"
   - Click "Enable"
4. **Get Project ID**: 
   - Project ID will be in format: `your-project-name-12345`
   - Copy this ID

### **Step 2: Get API Key**

1. **Go to API Credentials**:
   - In Google Cloud Console
   - "APIs & Services" → "Credentials"
2. **Create API Key**:
   - Click "+ CREATE CREDENTIALS"
   - Select "API key"
   - Copy the API key
3. **Restrict API Key** (Recommended):
   - Click on the created API key
   - Under "API restrictions", select "Restrict key"
   - Choose "Gemini API"
   - Save

### **Step 3: Update VS Code Settings**

अब `.vscode/settings.json` file में अपना **actual Project ID** और **API Key** डालें:

```json
{
    "// APS Dream Home - Gemini Code Assist Configuration": "",
    "google.gemini.enable": true,
    "google.gemini.projectId": "YOUR-ACTUAL-PROJECT-ID-HERE",
    "google.gemini.apiKey": "YOUR-ACTUAL-API-KEY-HERE",
    "google.gemini.model": "gemini-1.5-flash",
    "google.gemini.maxTokens": 8192,
    "google.gemini.temperature": 0.7,
    "gemini-code-assist.projectId": "YOUR-ACTUAL-PROJECT-ID-HERE",
    "gemini-code-assist.apiKey": "YOUR-ACTUAL-API-KEY-HERE",
    "gemini-code-assist.enabled": true,
    "gemini-code-assist.model": "gemini-1.5-flash",
    "gemini-code-assist.maxTokens": 8192
}
```

### **Step 4: Install Gemini Code Assist Extension**

1. **Open VS Code Extensions** (Ctrl+Shift+X)
2. **Search for**: "Gemini Code Assist"
3. **Install**: Official Google extension
4. **Restart VS Code**

### **Step 5: Configure Extension**

1. **Open Command Palette** (Ctrl+Shift+P)
2. **Type**: "Gemini Code Assist: Configure"
3. **Enter Project ID**: अपना valid project ID डालें
4. **Enter API Key**: अपनी API key डालें
5. **Select Model**: "gemini-1.5-flash"

### **Step 6: Alternative Configuration Methods**

#### **Method A: Environment Variables**
```bash
# Set in Windows Environment Variables
GOOGLE_GEMINI_PROJECT_ID=your-project-id
GOOGLE_GEMINI_API_KEY=your-api-key
```

#### **Method B: .env File**
Create `.env` file in project root:
```env
GOOGLE_GEMINI_PROJECT_ID=your-project-id
GOOGLE_GEMINI_API_KEY=your-api-key
GOOGLE_GEMINI_MODEL=gemini-1.5-flash
```

#### **Method C: VS Code Settings UI**
1. **File** → **Preferences** → **Settings**
2. **Search**: "gemini"
3. **Fill in**: Project ID and API Key fields

### **Step 7: Verify Configuration**

1. **Check Status Bar**: Gemini icon should appear
2. **Test Integration**:
   - Open any PHP file
   - Try AI completion (Ctrl+Space)
   - Check for Gemini suggestions

## 🛠️ **Troubleshooting**

### **Issue 1: Invalid Project ID**
- **Solution**: Verify project ID from Google Cloud Console
- **Check**: Project ID format (no spaces, correct format)

### **Issue 2: API Key Not Working**
- **Solution**: 
  - Regenerate API key
  - Check API restrictions
  - Verify Gemini API is enabled

### **Issue 3: Permission Denied**
- **Solution**:
  - Check user permissions in Google Cloud
  - Verify IAM roles (Editor, Owner, or Gemini User)
  - Check billing is enabled

### **Issue 4: Extension Not Working**
- **Solution**:
  - Restart VS Code
  - Disable other AI extensions
  - Check extension version

### **Issue 5: Rate Limit Exceeded**
- **Solution**:
  - Check API quota in Google Cloud
  - Upgrade to paid plan if needed
  - Reduce request frequency

## 📋 **Required Permissions**

### **Google Cloud IAM Roles Needed:**
- **Owner** or **Editor** (full access)
- **Gemini User** (minimum required)
- **Service Usage Consumer** (API usage)

### **APIs to Enable:**
- **Gemini API**
- **Cloud Resource Manager API**
- **IAM Service Account Credentials API**

## 🔄 **Quick Fix Commands**

### **Windows PowerShell:**
```powershell
# Set environment variables
$env:GOOGLE_GEMINI_PROJECT_ID = "your-project-id"
$env:GOOGLE_GEMINI_API_KEY = "your-api-key"

# Restart VS Code with new environment
code --reload
```

### **Check Configuration:**
```powershell
# Verify environment variables
Get-ChildItem Env: | Where-Object Name -like "*GEMINI*"
```

## 🎯 **Best Practices**

### **Security:**
1. **Never commit API keys** to Git
2. **Use environment variables** for production
3. **Restrict API key** to specific APIs
4. **Rotate API keys** regularly

### **Performance:**
1. **Use appropriate model** (flash for speed, pro for quality)
2. **Set reasonable token limits**
3. **Cache responses** when possible
4. **Monitor API usage**

### **Development:**
1. **Test with small prompts** first
2. **Use temperature 0.7** for balanced responses
3. **Enable debug mode** for troubleshooting
4. **Keep extension updated**

## 📞 **Support Resources**

### **Official Documentation:**
- [Gemini API Docs](https://ai.google.dev/docs)
- [VS Code Extension](https://marketplace.visualstudio.com/items?itemName=Google.gemini-code-assist)
- [Google Cloud Console](https://console.cloud.google.com/)

### **Common Issues:**
- Project ID format errors
- API key restrictions
- Permission issues
- Extension conflicts

## 🏆 **Success Verification**

### **Working Configuration Signs:**
✅ Gemini icon in status bar
✅ AI suggestions appearing
✅ No error messages
✅ Code completion working
✅ Chat functionality active

### **Test Commands:**
```php
// Try typing this and see if Gemini suggests completion
function calculateEMI($principal, $rate, $months) {
    // Gemini should suggest EMI formula here
}
```

---

## 🎉 **Final Steps**

1. **Update settings.json** with your actual credentials
2. **Restart VS Code**
3. **Test with a simple PHP file**
4. **Verify AI suggestions work**
5. **Enjoy AI-powered coding!**

**अगर आपको कोई problem आए तो मुझे बताएं! मैं full help करूंगा।** 🚀

---

*Last Updated: March 30, 2026*
*Project: APS Dream Home*
*Issue: Gemini Code Assist Configuration*
