# Fluid Checkout Customizer - Deployment Guide

## Overview

This guide provides step-by-step instructions for deploying the Fluid Checkout Customizer integration to the Dancewear Live production server.

## Prerequisites

Before deploying, ensure you have:

1. ✅ SSH access to the server
2. ✅ Git installed locally
3. ✅ Proper credentials for the remote server
4. ✅ Backup of the current child theme (recommended)

## Server Information

- **Server IP**: 35.198.155.162
- **SSH Port**: 18705
- **SSH User**: dancewearcouk
- **SSH Command**: `ssh dancewearcouk@35.198.155.162 -p 18705`

## Deployment Steps

### Step 1: Verify Local Changes

Before deploying, verify all files are present and correct:

```bash
# Check that all required files exist
ls -la blaze-blocksy/includes/customization/fluid-checkout-customizer.php
ls -la blaze-blocksy/assets/js/fluid-checkout-customizer-preview.js
ls -la blaze-blocksy/docs/fluid-checkout-customizer-guide.md
ls -la blaze-blocksy/docs/fluid-checkout-element-map.md
```

### Step 2: Commit Changes to Git

```bash
# Navigate to the child theme directory
cd blaze-blocksy

# Check git status
git status

# Add all new and modified files
git add includes/customization/fluid-checkout-customizer.php
git add assets/js/fluid-checkout-customizer-preview.js
git add docs/fluid-checkout-customizer-guide.md
git add docs/fluid-checkout-element-map.md
git add docs/fluid-checkout-deployment-guide.md
git add functions.php

# Commit with a descriptive message
git commit -m "feat: Add comprehensive Fluid Checkout Customizer integration

- Add Fluid Checkout Customizer with 6 styling sections
- Implement General Colors section with 8 color controls
- Add Typography sections for 5 element types (heading, body, label, placeholder, button)
- Create Form Elements section with input styling controls
- Implement Buttons section with primary button and hover states
- Add Spacing section with padding and margin controls
- Create Borders section with width, color, style, and radius controls
- Include live preview JavaScript for real-time customizer updates
- Add comprehensive documentation and element mapping
- Integrate with Blocksy theme customizer API
- Support CSS variables for seamless Fluid Checkout integration"

# Push to remote repository (if applicable)
# git push origin main
```

### Step 3: Create Backup on Server

Before uploading new files, create a backup of the current child theme:

```bash
# Connect to server and create backup
ssh dancewearcouk@35.198.155.162 -p 18705 "cd wp-content/themes && tar -czf blaze-blocksy-backup-$(date +%Y%m%d-%H%M%S).tar.gz blaze-blocksy/"
```

### Step 4: Upload Files via SCP

Upload the modified child theme files to the server:

```bash
# Upload the entire customization directory
scp -P 18705 -r blaze-blocksy/includes/customization/fluid-checkout-customizer.php \
  dancewearcouk@35.198.155.162:/path/to/wp-content/themes/blaze-blocksy/includes/customization/

# Upload the JavaScript file
scp -P 18705 blaze-blocksy/assets/js/fluid-checkout-customizer-preview.js \
  dancewearcouk@35.198.155.162:/path/to/wp-content/themes/blaze-blocksy/assets/js/

# Upload the updated functions.php
scp -P 18705 blaze-blocksy/functions.php \
  dancewearcouk@35.198.155.162:/path/to/wp-content/themes/blaze-blocksy/

# Upload documentation (optional but recommended)
scp -P 18705 -r blaze-blocksy/docs/ \
  dancewearcouk@35.198.155.162:/path/to/wp-content/themes/blaze-blocksy/
```

**Note**: Replace `/path/to/wp-content/themes/` with the actual path on the server. Common paths:
- `/home/dancewearcouk/public_html/wp-content/themes/`
- `/var/www/html/wp-content/themes/`
- `/usr/share/nginx/html/wp-content/themes/`

### Step 5: Set Correct Permissions

Ensure files have the correct permissions:

```bash
# Connect to server
ssh dancewearcouk@35.198.155.162 -p 18705

# Navigate to theme directory
cd /path/to/wp-content/themes/blaze-blocksy

# Set correct permissions for PHP files
find . -type f -name "*.php" -exec chmod 644 {} \;

# Set correct permissions for JS files
find . -type f -name "*.js" -exec chmod 644 {} \;

# Set correct permissions for directories
find . -type d -exec chmod 755 {} \;

# Exit SSH
exit
```

### Step 6: Clear WordPress Cache

Clear all caches to ensure changes take effect:

```bash
# If using WP-CLI on the server
ssh dancewearcouk@35.198.155.162 -p 18705 "cd /path/to/wordpress && wp cache flush"

# If using a caching plugin, clear it via SSH or WordPress admin
```

Alternatively, clear cache via WordPress admin:
1. Log in to WordPress admin
2. Navigate to caching plugin settings (e.g., WP Rocket, W3 Total Cache)
3. Click "Clear All Cache" or similar option

### Step 7: Verify Deployment

1. **Check File Upload**:
   ```bash
   ssh dancewearcouk@35.198.155.162 -p 18705 "ls -la /path/to/wp-content/themes/blaze-blocksy/includes/customization/fluid-checkout-customizer.php"
   ```

2. **Check WordPress Admin**:
   - Log in to https://cart.dancewear.blz.au/wp-admin
   - Navigate to Appearance > Customize
   - Look for "Fluid Checkout Styling" panel
   - Verify all sections are present

3. **Test Customizer**:
   - Open a customizer section
   - Make a test change (e.g., change primary color)
   - Verify live preview works
   - Click "Publish" to save
   - Visit checkout page to confirm changes applied

### Step 8: Test on Checkout Page

1. Add a product to cart
2. Navigate to checkout page
3. Verify customizer styles are applied
4. Test responsiveness on different screen sizes
5. Check browser console for JavaScript errors

## Rollback Procedure

If issues occur, rollback to the previous version:

```bash
# Connect to server
ssh dancewearcouk@35.198.155.162 -p 18705

# Navigate to themes directory
cd /path/to/wp-content/themes

# Remove current version
rm -rf blaze-blocksy

# Extract backup
tar -xzf blaze-blocksy-backup-YYYYMMDD-HHMMSS.tar.gz

# Clear cache
wp cache flush

# Exit
exit
```

## Troubleshooting

### Issue: Customizer Panel Not Appearing

**Solution**:
1. Verify `fluid-checkout-customizer.php` is uploaded correctly
2. Check `functions.php` includes the file in the required files array
3. Clear WordPress cache
4. Check PHP error logs for syntax errors

### Issue: Styles Not Applying

**Solution**:
1. Verify you're on the checkout page
2. Clear browser cache
3. Check browser developer tools for CSS conflicts
4. Verify Fluid Checkout plugin is active
5. Check that CSS is being output in page source

### Issue: Live Preview Not Working

**Solution**:
1. Verify `fluid-checkout-customizer-preview.js` is uploaded
2. Check browser console for JavaScript errors
3. Ensure jQuery is loaded
4. Verify file path in `enqueue_preview_scripts()` method

### Issue: Permission Denied Errors

**Solution**:
```bash
# Fix file permissions
ssh dancewearcouk@35.198.155.162 -p 18705
cd /path/to/wp-content/themes/blaze-blocksy
chmod -R 755 .
find . -type f -exec chmod 644 {} \;
```

## Post-Deployment Checklist

- [ ] All files uploaded successfully
- [ ] File permissions set correctly
- [ ] WordPress cache cleared
- [ ] Browser cache cleared
- [ ] Customizer panel appears in WordPress admin
- [ ] All sections visible in customizer
- [ ] Live preview working
- [ ] Changes save correctly
- [ ] Styles apply on checkout page
- [ ] No JavaScript errors in console
- [ ] No PHP errors in logs
- [ ] Responsive design works on mobile/tablet
- [ ] Documentation uploaded (optional)
- [ ] Backup created and verified

## Security Notes

1. **Never commit credentials** to Git repository
2. **Use SSH keys** instead of passwords when possible
3. **Limit file permissions** to minimum required
4. **Keep backups** of all deployments
5. **Test in staging** environment first (if available)

## Support

For issues or questions:
1. Check PHP error logs: `/path/to/logs/error.log`
2. Check WordPress debug log: `wp-content/debug.log`
3. Review browser console for JavaScript errors
4. Contact development team with error details

## Changelog

### Version 1.0.0 - Initial Deployment
- Fluid Checkout Customizer integration
- 6 styling sections with comprehensive controls
- Live preview support
- CSS variables integration
- Complete documentation

## Next Steps

After successful deployment:

1. **Train administrators** on using the customizer
2. **Document brand guidelines** for color and typography choices
3. **Create presets** for common styling scenarios
4. **Monitor performance** impact of customizer CSS
5. **Gather feedback** from users and stakeholders
6. **Plan enhancements** based on usage patterns

## Additional Resources

- [Fluid Checkout Customizer Guide](./fluid-checkout-customizer-guide.md)
- [Fluid Checkout Element Map](./fluid-checkout-element-map.md)
- [WordPress Customizer API Documentation](https://developer.wordpress.org/themes/customize-api/)
- [Blocksy Theme Documentation](https://creativethemes.com/blocksy/docs/)

