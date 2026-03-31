# Screenshots Folder

Please paste your screenshots here with these exact filenames:

1. **login.png** - Login page screenshot
2. **dashboard.png** - Main dashboard screenshot
3. **poultry-dashboard.png** - Poultry operations dashboard
4. **financial-dashboard.png** - Financial dashboard
5. **batch-management.png** - Batch listing page
6. **feed-recording.png** - Feed entry form

## How to Capture Screenshots

1. Login to: http://localhost/farmapp/login (admin / admin123)
2. Visit each page and press `Ctrl+Shift+P` → "Capture full size screenshot"
3. Save with the exact filename above
4. Paste into this folder

## Pages to Capture

- http://localhost/farmapp/login → login.png
- http://localhost/farmapp/ → dashboard.png
- http://localhost/farmapp/poultry → poultry-dashboard.png
- http://localhost/farmapp/financial → financial-dashboard.png
- http://localhost/farmapp/batches → batch-management.png
- http://localhost/farmapp/feed/create → feed-recording.png

After adding screenshots, run:
```bash
git add screenshots/
git commit -m "Add system screenshots"
git push origin master
```
