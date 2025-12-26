# Video Threat Detection - Testing Checklist

## Pre-Testing Setup

### Environment Verification
- [ ] Python 3.8+ installed
- [ ] PHP 8.1+ installed
- [ ] Node.js and npm installed
- [ ] All Python dependencies installed (`pip install -r requirements.txt`)
- [ ] Laravel dependencies installed (`composer install`)
- [ ] Assets compiled (`npm run build`)
- [ ] `.env` file configured with `VIDEO_THREAT_API_URL`

### Service Status
- [ ] Flask API running on port 5003
- [ ] Laravel application running
- [ ] No port conflicts
- [ ] Firewall allows local connections

## Flask API Testing

### Health Check
- [ ] Visit `http://127.0.0.1:5003/api/video/health`
- [ ] Response: `{"status": "healthy", "message": "..."}`
- [ ] HTTP status code: 200

### Status Check
- [ ] Visit `http://127.0.0.1:5003/api/video/status`
- [ ] Response includes:
  - [ ] `status: "active"`
  - [ ] `object_detector_loaded: true`
  - [ ] `threat_detector_loaded: true`
  - [ ] `tracker_active: true`

### CORS Headers
- [ ] Response includes `Access-Control-Allow-Origin: *`
- [ ] Response includes `Access-Control-Allow-Methods`
- [ ] Response includes `Access-Control-Allow-Headers`

## Laravel Integration Testing

### Route Access
- [ ] Navigate to `/admin/management/video-threat`
- [ ] Page loads without errors
- [ ] No 404 or 500 errors
- [ ] Sidebar shows "Video Threat Detection" menu item

### Dashboard UI
- [ ] All 4 status cards visible
- [ ] Video feed container present
- [ ] Detection results panel visible
- [ ] Detection history table visible
- [ ] Start/Stop buttons present
- [ ] Camera source toggle buttons present

### API Proxy Endpoints
Test via browser console or Postman:

- [ ] GET `/admin/management/video-threat/status`
  - Returns API status
  - No errors

## PC Camera Testing

### Camera Access
- [ ] Click "Start Detection"
- [ ] Browser requests camera permission
- [ ] Grant permission
- [ ] Video feed appears
- [ ] No "Camera access denied" error

### Video Display
- [ ] Video stream is smooth
- [ ] No lag or freezing
- [ ] Video resolution is acceptable
- [ ] Canvas overlay is positioned correctly

### Detection Functionality
- [ ] Place object in view
- [ ] Object is detected (bounding box appears)
- [ ] Object class name is displayed
- [ ] Confidence score is shown
- [ ] Detection appears in results panel

### Left-Behind Object Detection
- [ ] Place object in view
- [ ] Keep object stationary for 5+ seconds
- [ ] Object marked as "LEFT BEHIND"
- [ ] Bounding box color changes (to red/orange)
- [ ] Alert appears in results panel
- [ ] Object count increases

### Statistics Updates
- [ ] "Frames Processed" counter increases
- [ ] FPS counter shows realistic value (8-12 FPS)
- [ ] Latency counter shows reasonable values (<500ms)
- [ ] "Left-Behind Objects" count updates
- [ ] "Last Object Time" updates to "Just now"

### Stop Detection
- [ ] Click "Stop Detection"
- [ ] Video stream stops
- [ ] Detection status changes to "Inactive"
- [ ] Camera status shows "disconnected"
- [ ] No errors in console

## ESP32-CAM Testing (If Available)

### Connection Setup
- [ ] ESP32-CAM powered on
- [ ] ESP32-CAM connected to WiFi
- [ ] IP address obtained
- [ ] Stream accessible at `http://ESP32_IP/stream`

### Dashboard Connection
- [ ] Select "ESP32-CAM" option
- [ ] Enter ESP32 IP address
- [ ] Click "Connect"
- [ ] Click "Start Detection"
- [ ] ESP32 stream appears
- [ ] No connection errors

### ESP32 Detection
- [ ] Objects detected from ESP32 stream
- [ ] Bounding boxes drawn correctly
- [ ] Detection results appear
- [ ] Statistics update
- [ ] FPS shows ~5 FPS (expected for ESP32)

## Detection Results Panel

### Object Detections
- [ ] New detections appear at top
- [ ] Timestamp is correct
- [ ] Object count is accurate
- [ ] Alert styling is appropriate (yellow/orange)

### Threat Detections
- [ ] Threat alerts appear (if triggered)
- [ ] Threat type is displayed
- [ ] Confidence percentage shown
- [ ] Alert styling is appropriate (red)
- [ ] Timestamp is correct

### Clear Functionality
- [ ] Click "Clear" button
- [ ] Results panel clears
- [ ] "No detections yet" message appears
- [ ] No errors

## Detection History Table

### History Logging
- [ ] Detections appear in history table
- [ ] Newest detections at top
- [ ] Time column shows correct time
- [ ] Type column shows badge (OBJECT/THREAT)
- [ ] Details column shows relevant info
- [ ] Confidence column shows percentage (for threats)
- [ ] Status column shows "New" badge

### Table Functionality
- [ ] Table scrolls if many entries
- [ ] Rows are readable
- [ ] No layout issues

## Error Handling

### API Unavailable
- [ ] Stop Flask API
- [ ] Try to start detection
- [ ] Error message appears
- [ ] No crash or freeze
- [ ] User can retry after restarting API

### Camera Denied
- [ ] Deny camera permission
- [ ] Appropriate error message shown
- [ ] Instructions provided
- [ ] Can retry after granting permission

### Invalid ESP32 IP
- [ ] Enter invalid IP (e.g., "999.999.999.999")
- [ ] Try to connect
- [ ] Error message appears
- [ ] No crash

### Network Issues
- [ ] Disconnect network briefly
- [ ] Detection continues gracefully
- [ ] Reconnects when network returns
- [ ] No data loss

## Performance Testing

### Frame Rate
- [ ] PC Camera: 8-12 FPS achieved
- [ ] ESP32-CAM: 4-6 FPS achieved
- [ ] No significant drops
- [ ] Consistent performance

### Latency
- [ ] PC Camera: <300ms average
- [ ] ESP32-CAM: <500ms average
- [ ] No excessive delays
- [ ] Real-time feel maintained

### Resource Usage
- [ ] CPU usage reasonable (<50% on modern hardware)
- [ ] Memory usage stable (no leaks)
- [ ] Browser responsive
- [ ] No system slowdown

### Long-Running Test
- [ ] Run detection for 10+ minutes
- [ ] No crashes
- [ ] No memory leaks
- [ ] Performance remains stable
- [ ] All features still work

## Browser Compatibility

### Chrome/Edge
- [ ] Dashboard loads
- [ ] Camera access works
- [ ] Detections work
- [ ] No console errors

### Firefox
- [ ] Dashboard loads
- [ ] Camera access works
- [ ] Detections work
- [ ] No console errors

### Safari (if available)
- [ ] Dashboard loads
- [ ] Camera access works
- [ ] Detections work
- [ ] No console errors

## Mobile Responsiveness

### Mobile View
- [ ] Dashboard is responsive
- [ ] Cards stack vertically
- [ ] Video feed scales properly
- [ ] Buttons are accessible
- [ ] No horizontal scroll

### Tablet View
- [ ] Layout adjusts appropriately
- [ ] All features accessible
- [ ] No UI issues

## Logging & Monitoring

### Laravel Logs
- [ ] Check `storage/logs/laravel.log`
- [ ] Left-behind objects logged
- [ ] Threats logged
- [ ] No unexpected errors

### Flask Logs
- [ ] Check terminal output
- [ ] Requests logged
- [ ] Detections logged
- [ ] No errors or warnings

### Browser Console
- [ ] No JavaScript errors
- [ ] No network errors
- [ ] Appropriate log messages
- [ ] No warnings

## Security Testing

### CSRF Protection
- [ ] POST requests include CSRF token
- [ ] Requests succeed
- [ ] No CSRF errors

### Input Validation
- [ ] Invalid frame data rejected
- [ ] Appropriate error messages
- [ ] No crashes

### API Authentication
- [ ] Only authenticated users can access
- [ ] Redirect to login if not authenticated
- [ ] Session maintained

## Final Verification

### Complete Workflow
- [ ] Start Flask API
- [ ] Start Laravel app
- [ ] Login to admin panel
- [ ] Navigate to Video Threat Detection
- [ ] Start detection (PC camera)
- [ ] Detect objects
- [ ] Detect left-behind objects
- [ ] View results
- [ ] Check history
- [ ] Stop detection
- [ ] Switch to ESP32-CAM (if available)
- [ ] Repeat detection tests
- [ ] Stop detection
- [ ] Logout

### Documentation
- [ ] README files are clear
- [ ] Quick start guide works
- [ ] All steps documented
- [ ] Troubleshooting section helpful

### Code Quality
- [ ] No syntax errors
- [ ] No linting errors
- [ ] Code is readable
- [ ] Comments are helpful
- [ ] Follows conventions

## Sign-Off

- [ ] All critical tests passed
- [ ] All features working
- [ ] Performance acceptable
- [ ] Documentation complete
- [ ] Ready for deployment

---

**Tested By:** ___________________

**Date:** ___________________

**Status:** ⬜ PASS  ⬜ FAIL  ⬜ NEEDS WORK

**Notes:**
```
[Add any additional notes or issues found during testing]
```

