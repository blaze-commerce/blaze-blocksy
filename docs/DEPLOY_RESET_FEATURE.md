# Deployment Guide: Fluid Checkout Reset Feature

## Overview
This guide provides step-by-step instructions for deploying the "Reset to Default" feature to the cart.dancewear.blz.au server.

---

## 📋 Pre-Deployment Checklist

- [x] Local file modified: `blaze-blocksy/includes/customization/fluid-checkout-customizer.php`
- [x] Feature tested locally (if applicable)
- [x] Documentation created
- [ ] Server backup created
- [ ] Deployment executed
- [ ] Feature tested on live site
- [ ] Cache cleared

---

## 🚀 Deployment Steps

### Step 1: Create Server Backup (RECOMMENDED)

Before deploying, create a backup of the current file on the server:

```powershell
# Connect to server and create backup
ssh dancewearcouk@35.198.155.162 -p 18705

# Once connected, run:
cd /public/wp-content/themes/blaze-blocksy/includes/customization/
cp fluid-checkout-customizer.php fluid-checkout-customizer.php.backup-$(date +%Y%m%d-%H%M%S)

# Verify backup was created
ls -la fluid-checkout-customizer.php*

# Exit SSH
exit
```

**Password**: `Dancewear2024!`

---

### Step 2: Upload Modified File via SCP

#### Option A: Using SCP Command (Windows PowerShell)

```powershell
# Navigate to the project directory
cd "c:\Users\Lance\Documents\BLAZECOMMERCE_TASKS\dancewear live create styling options for fluid checkout in blocksy customizer"

# Upload the file
scp -P 18705 blaze-blocksy/includes/customization/fluid-checkout-customizer.php dancewearcouk@35.198.155.162:/public/wp-content/themes/blaze-blocksy/includes/customization/

# When prompted, enter password: Dancewear2024!
```

#### Option B: Using WinSCP (GUI)

1. Open WinSCP
2. Create new connection:
   - **Protocol**: SFTP
   - **Host**: 35.198.155.162
   - **Port**: 18705
   - **Username**: dancewearcouk
   - **Password**: Dancewear2024!
3. Connect to server
4. Navigate to: `/public/wp-content/themes/blaze-blocksy/includes/customization/`
5. Upload `fluid-checkout-customizer.php` from local directory
6. Confirm overwrite when prompted

---

### Step 3: Verify File Upload

```powershell
# Connect to server
ssh dancewearcouk@35.198.155.162 -p 18705

# Verify file was uploaded and check file size
ls -lh /public/wp-content/themes/blaze-blocksy/includes/customization/fluid-checkout-customizer.php

# Check file permissions (should be 644)
stat /public/wp-content/themes/blaze-blocksy/includes/customization/fluid-checkout-customizer.php

# Exit SSH
exit
```

**Expected file size**: ~45-50 KB (increased from ~40 KB due to new code)

---

### Step 4: Set Correct Permissions (if needed)

```powershell
# Connect to server
ssh dancewearcouk@35.198.155.162 -p 18705

# Set correct permissions
chmod 644 /public/wp-content/themes/blaze-blocksy/includes/customization/fluid-checkout-customizer.php

# Verify permissions
ls -la /public/wp-content/themes/blaze-blocksy/includes/customization/fluid-checkout-customizer.php

# Exit SSH
exit
```

---

### Step 5: Clear Kinsta Cache

#### Via WordPress Admin (RECOMMENDED)

1. Log in to WordPress admin: https://cart.dancewear.blz.au/wp-admin/
   - **Username**: blaze_dev1
   - **Password**: D7D2Lvroeew4iIX^Pu1$*e3h

2. Navigate to: **Kinsta Cache** (in the admin sidebar)

3. Click: **Clear All Cache**

4. Wait for confirmation message

#### Via SSH (Alternative)

```powershell
# Connect to server
ssh dancewearcouk@35.198.155.162 -p 18705

# Clear cache using WP-CLI (if available)
cd /public
wp cache flush

# Exit SSH
exit
```

---

## ✅ Post-Deployment Testing

### Test 1: Verify Reset Section Appears

1. Navigate to: https://cart.dancewear.blz.au/wp-admin/customize.php
2. Click on: **Fluid Checkout Styling** panel
3. Verify: **"Reset Settings"** section appears at the top of the panel
4. Verify: Section contains reset button and warning message

**Expected Result**: Reset Settings section is visible with a red warning message.

---

### Test 2: Test Reset Functionality

1. In the customizer, make some changes to Fluid Checkout styles:
   - Change a color (e.g., Primary Color)
   - Change a font size
   - Change spacing values

2. Click: **Publish** to save changes

3. Navigate to: **Reset Settings** section

4. Click: **"Reset All Styles to Default"** button

5. Confirm: Click "OK" in the confirmation dialog

6. Observe:
   - Button text changes to "Resetting..."
   - Button becomes disabled
   - After ~1 second, button shows "Reset Complete!" with green background
   - Customizer reloads automatically

7. Verify: All changes have been reverted to default values

**Expected Result**: All Fluid Checkout styling options are reset to defaults.

---

### Test 3: Verify Live Preview Updates

1. Navigate to: **Fluid Checkout Styling → General Colors**

2. Change: **Primary Color** to a different color (e.g., red #ff0000)

3. Observe: Live preview updates immediately

4. Navigate to: **Reset Settings**

5. Click: **"Reset All Styles to Default"**

6. Confirm reset

7. After reload, verify: Primary Color is back to default (#0047e3)

**Expected Result**: Live preview reflects default values after reset.

---

### Test 4: Check Browser Console

1. Open browser developer tools (F12)

2. Navigate to: **Console** tab

3. Perform reset operation

4. Verify: No JavaScript errors appear

**Expected Result**: No errors in console.

---

### Test 5: Verify AJAX Response

1. Open browser developer tools (F12)

2. Navigate to: **Network** tab

3. Filter by: **XHR** requests

4. Perform reset operation

5. Find: Request to `admin-ajax.php` with action `blocksy_fc_reset_settings`

6. Check response:
   ```json
   {
     "success": true,
     "data": {
       "message": "Successfully reset X Fluid Checkout styling options",
       "count": X
     }
   }
   ```

**Expected Result**: AJAX request succeeds with success response.

---

## 🔧 Troubleshooting

### Issue: Reset Button Not Appearing

**Possible Causes**:
- File not uploaded correctly
- Cache not cleared
- Fluid Checkout plugin not active

**Solutions**:
1. Verify file upload (Step 3)
2. Clear all caches (Step 5)
3. Check Fluid Checkout plugin status in WordPress admin
4. Check PHP error logs

---

### Issue: AJAX Request Fails

**Possible Causes**:
- Nonce verification failed
- User lacks permissions
- Server error

**Solutions**:
1. Check browser console for error messages
2. Verify user has `edit_theme_options` capability
3. Check server error logs:
   ```bash
   ssh dancewearcouk@35.198.155.162 -p 18705
   tail -n 50 /public/wp-content/debug.log
   ```

---

### Issue: Settings Not Resetting

**Possible Causes**:
- Theme mods not using correct prefix
- Database issue
- Cache issue

**Solutions**:
1. Verify AJAX response shows count > 0
2. Check database for theme mods:
   ```bash
   ssh dancewearcouk@35.198.155.162 -p 18705
   wp option get theme_mods_blaze-blocksy | grep blocksy_fc_
   ```
3. Clear all caches again

---

### Issue: Customizer Not Reloading

**Possible Causes**:
- JavaScript error
- Browser cache

**Solutions**:
1. Check browser console for errors
2. Hard refresh browser (Ctrl+Shift+R)
3. Clear browser cache

---

## 📊 Verification Checklist

After deployment, verify the following:

- [ ] File uploaded successfully to server
- [ ] File permissions are correct (644)
- [ ] Kinsta cache cleared
- [ ] Reset Settings section appears in customizer
- [ ] Reset button is visible and styled correctly
- [ ] Warning message is displayed
- [ ] Confirmation dialog appears on button click
- [ ] AJAX request succeeds
- [ ] All settings are reset to defaults
- [ ] Customizer reloads after reset
- [ ] Live preview updates correctly
- [ ] No JavaScript errors in console
- [ ] No PHP errors in logs

---

## 🔄 Rollback Procedure

If issues occur, rollback to the previous version:

```powershell
# Connect to server
ssh dancewearcouk@35.198.155.162 -p 18705

# Navigate to customization directory
cd /public/wp-content/themes/blaze-blocksy/includes/customization/

# List backups
ls -la fluid-checkout-customizer.php.backup-*

# Restore from backup (replace YYYYMMDD-HHMMSS with actual timestamp)
cp fluid-checkout-customizer.php.backup-YYYYMMDD-HHMMSS fluid-checkout-customizer.php

# Verify restoration
ls -la fluid-checkout-customizer.php

# Exit SSH
exit
```

Then clear cache and verify the site is working correctly.

---

## 📝 Deployment Log

**Date**: 2024-11-08  
**Deployed By**: [Your Name]  
**File Modified**: `blaze-blocksy/includes/customization/fluid-checkout-customizer.php`  
**Lines Added**: ~155 lines  
**Feature**: Reset to Default button for Fluid Checkout Customizer  
**Status**: ⏳ Pending Deployment

### Deployment Notes:
- [ ] Backup created: `fluid-checkout-customizer.php.backup-YYYYMMDD-HHMMSS`
- [ ] File uploaded successfully
- [ ] Permissions set to 644
- [ ] Cache cleared
- [ ] Feature tested and verified
- [ ] No errors encountered

---

## 📞 Support

For issues or questions:
- **Email**: alan@blazecommerce.io
- **Documentation**: `blaze-blocksy/docs/FLUID_CHECKOUT_RESET_FEATURE.md`

---

## ✨ Summary

The reset feature has been successfully implemented and is ready for deployment. Follow the steps above to deploy to the live server and verify functionality.

**Key Benefits**:
- ✅ Easy reset of all Fluid Checkout styling options
- ✅ Confirmation dialog prevents accidental resets
- ✅ Automatic customizer reload after reset
- ✅ Secure implementation with nonce verification
- ✅ User-friendly interface with clear feedback

