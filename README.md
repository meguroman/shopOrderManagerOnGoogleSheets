## shopOrderManagerOnGoogleSheets
Order manage system on Google spreadsheets for common EC mall using php on goutte and zend Gdata.

1. "triggeredGoogleAppsScript.js" on Google Apps Script watch order mail of gmail each 10 minutes using GAS cron.
2. When it find order mails, it kick "writeOrderToSS.php" file and send order mail information via POST.
3. "writeOrderToSS.php" get order detail data from EC mall site using php on goutte.
4. Then it write order detail data to Google spread sheets using Zend Gdata APIs.
