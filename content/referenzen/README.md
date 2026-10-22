# Referenzen (Texte)

Eine Datei pro Referenz. Stand: Export der Testseite vom 22.10.2026 (Inhalt der Felder
unverändert, per Prüfsumme gegen die Live-Daten verglichen).

Aufbau: Kopfbereich mit den einzeiligen Feldern (`untertitel`, `kunde`, `branche`, `jahr`,
`leistungen`, `url`, `auszug`), danach die drei Textfelder, jeweils eingeleitet mit
`@@ ausgangslage`, `@@ vorgehen`, `@@ ergebnis`.

`seo_beschreibung` ist neu und steht noch nicht in WordPress: Meta-Beschreibung mit
höchstens 155 Zeichen (die bisherige Beschreibung entsteht aus dem Auszug und ist zu lang).
Wird mit der Einspielung in das Feld `_gebhart_seo_desc` übernommen.

Die Dateien sind Quelle und Sicherung, WordPress liest sie nicht selbst ein. Änderungen
werden im Backend (oder per Schnittstelle) in die Referenz übernommen.
