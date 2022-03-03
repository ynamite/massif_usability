# massif_usability

Package für REDAXO CMS >= 5.10.0

temporäres Ersatz-Plugin für yform_usability für yform >=4 – bietet ähnliche Funktionalität wie yform usability

Momentan verfügbar:

-   Datensatz kopieren (befindet sich im Aktions-Menü rechts)
-   Status on-/offline Toggle – **wichtig**: Feld "status" muss existieren. Werte: `0=offline,1=online`)
-   benutzerdefinierte Toggles

_Leider bis jetzt ohne Drag&Drop – sawry!_

---

### Beispiel für ein benutzerdefiniertes Toggle

über `registerCustomToggle` kann ein eigenes Toggle erstellt werden, ähnlich dem Status-Toggle.

```
massif_usability::registerCustomToggle(table_name: String, field_name: String, off_state: String (HTML allowed), on_state: String (HTML allowed));
```

zBsp. um einen Datensatz als Highlight zu markieren (bei Events zum Beispiel):

```
massif_usability::registerCustomToggle('rex_yf_event', 'is_highlight', '<i class="fa fa-star"></i>', '<i class="fa fa-star"></i>');
```

## Bugs & Feature-Requests

Gerne als Issue oder im Redaxo Slack Channel

## Last Changes

### Version 1.0.1 // 03.03.2022

-   replaced rex_sql with yorm in api functions
