# JSPrintManager Troubleshooting Guide

## Common Issues and Solutions

### 1. "JSPrintManager not available" Error

This error occurs when the JSPrintManager scripts are not loaded or the JSPrintManager service is not running.

**Solutions:**

#### Check if JSPrintManager is Installed
- Download and install JSPrintManager from: https://www.neodynamic.com/products/printing/js-print-manager/
- Make sure the JSPrintManager service is running on your computer

#### Check Script Loading
1. Open browser Developer Tools (F12)
2. Go to the Console tab
3. Navigate to `/print-available` page
4. Look for any script loading errors
5. Check if the following scripts are loaded:
   - `/js/bluebird.min.js`
   - `/js/jquery-3.2.1.slim.min.js`
   - `/js/jsmanager/JSPrintManager.js`

#### Verify Service Status
1. **Windows:** Open Services (services.msc) and look for "Neodynamic JSPrintManager"
2. **Mac:** Check if JSPrintManager is running in Activity Monitor
3. **Linux:** Check if the JSPrintManager daemon is running

#### Test JSPrintManager Manually
1. Go to `/print-available` page
2. Click the "Test" button next to the printer dropdown
3. Check the browser console for detailed error messages

### 2. No Printers Showing in Dropdown

**Solutions:**

#### Check Printer Installation
- Make sure printers are properly installed on your system
- Test printing from other applications to verify printers work

#### Refresh Printers
- Click the "Refresh" button (↻) next to the printer dropdown
- Wait for the green status indicator

#### Check JSPrintManager Service
- Restart the JSPrintManager service
- Try refreshing the page

### 3. Print Jobs Not Working

**Solutions:**

#### Verify Printer Selection
- Make sure a printer is selected in the dropdown
- Try selecting a different printer

#### Check Printer Status
- Ensure the printer is online and has paper/toner
- Check for any printer error messages

#### Test Print Job
- Try printing a simple test page from your system
- Verify the printer can handle ZPL commands (for label printers)

### 4. Status Indicators

The print station shows different status indicators:

- 🟢 **Green dot**: JSPrintManager is connected and working
- 🟡 **Yellow dot**: JSPrintManager is loading/connecting
- 🔴 **Red dot**: JSPrintManager error or not available

### 5. Browser Console Debugging

To get detailed debugging information:

1. Open Developer Tools (F12)
2. Go to Console tab
3. Navigate to `/print-available`
4. Look for messages starting with "JSPrintManager" or "JSPM"
5. Check for any error messages in red

### 6. Common Error Messages

#### "JSPrintManager not loaded after 5 seconds"
- JSPrintManager scripts are not loading properly
- Check internet connection and script paths

#### "Failed to connect to JSPrintManager"
- JSPrintManager service is not running
- Firewall might be blocking the connection

#### "No printers available"
- No printers are installed on the system
- JSPrintManager can't detect the printers

### 7. Advanced Troubleshooting

#### Clear Browser Cache
1. Clear browser cache and cookies
2. Hard refresh the page (Ctrl+F5 or Cmd+Shift+R)

#### Check Firewall Settings
- Make sure JSPrintManager is allowed through firewall
- Default port is 22443 for HTTPS

#### Update JSPrintManager
- Download the latest version from the official website
- Uninstall old version before installing new one

### 8. Getting Help

If you're still having issues:

1. Check the browser console for error messages
2. Note your operating system and browser version
3. Check JSPrintManager version
4. Contact your system administrator

### 9. Alternative Solutions

If JSPrintManager continues to have issues:

1. Try using the regular "Print" button instead of "Send to Print Machine"
2. Use the browser's built-in print dialog
3. Export labels and print them manually

## Technical Details

### Required Files
- `public/js/bluebird.min.js`
- `public/js/jquery-3.2.1.slim.min.js`
- `public/js/jsmanager/JSPrintManager.js`

### Browser Compatibility
- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

### JSPrintManager Service
- Must be running on the local machine
- Communicates via WebSocket connection
- Requires proper certificates for HTTPS sites 