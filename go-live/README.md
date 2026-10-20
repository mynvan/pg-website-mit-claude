# Dateien für den Go-live

Diese Dateien gehören **nicht** ins Theme, sondern per FTP ins **Hauptverzeichnis** der Website (dort, wo `wp-config.php` liegt), sobald die Seite beim endgültigen Hoster unter philippgebhart.de läuft.

- `robots.txt` – erlaubt Suchmaschinen und KI-Crawlern den Zugriff, verweist auf die Sitemap.
- `llms.txt` – kompakte Übersicht für KI-Systeme (Leistungen, Preise, Referenzen, Beiträge).

Nach dem Hochladen prüfen:
1. https://philippgebhart.de/robots.txt und https://philippgebhart.de/llms.txt sind erreichbar.
2. https://philippgebhart.de/wp-sitemap.xml wird ausgeliefert (erst nach Entfernen von „Suchmaschinen davon abhalten …“ in den WordPress-Einstellungen).
3. llms.txt bei neuen Referenzen, Beiträgen oder Preisänderungen aktualisieren.
