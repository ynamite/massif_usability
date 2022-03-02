# massif_usability

Package für REDAXO CMS >= 5.10.0

temporäres Ersatz-Plugin für yform_usability für yform >=4 – bietet ähnliche Funktionalität wie yform usability

Momentan verfügbar:

-   Datensatz kopieren
-   on-/offline Toggle

Leider kein Drag&Drop – sawry!

### Beispiel für ein benutzerdefiniertes Toggle (wie Status offline/online Button)

```
massif_usability::registerCustomToggle(table_name: String, field_name: String, off_state: String (HTML allowed), on_state: String (HTML allowed));
```

zBsp:

```
massif_usability::registerCustomToggle('rex_yf_event', 'is_highlight', '<i class="fa fa-star"></i>', '<i class="fa fa-star"></i>');
```
