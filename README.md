# Ze Funnel - WordPress Conversational Funnel Plugin

Ein modernes WordPress-Plugin für conversational forms und qualification funnels mit Svelte-Frontend und Vue.js-Admin-Interface.

## 🚀 Quick Start

### Installation
1. Plugin-Ordner in `/wp-content/plugins/ze-funnel/` kopieren
2. Dependencies installieren: `npm install`
3. Assets bauen: `npm run build`
4. Plugin in WordPress aktivieren

### Development
```bash
# Development Server starten
npm run dev

# Admin-Interface entwickeln
npm run dev:admin

# Code formatieren
npm run format
npm run lint
```

## 🏗 Architektur

### Frontend (User-facing Funnels)
- **Framework:** Svelte 4 + Vite
- **Styling:** Vanilla CSS mit CSS Custom Properties
- **State Management:** Svelte Stores
- **Bundle:** Kompiliert zu `dist/frontend/ze-funnel.js|css`

### Admin-Interface (WordPress Backend)
- **Framework:** Vue.js 3 + Composition API
- **UI Library:** Shadcn-Vue Components
- **Build:** Vite + PostCSS
- **Bundle:** Kompiliert zu `dist/admin/ze-funnel-admin.js|css`

### Backend (PHP)
- **WordPress Integration:** Native Hooks & REST API
- **Database:** Custom Tables für Performance
- **API:** REST Endpoints unter `/wp-json/ze-funnel/v1/`
- **Admin:** WordPress Admin Pages & Meta Boxes

## 📊 Datenbank Schema

```sql
wp_ze_funnels          # Funnel-Konfigurationen
wp_ze_questions        # Fragen-Pool mit Optionen
wp_ze_submissions      # User-Antworten & Lead-Daten  
wp_ze_analytics        # Event-Tracking & Statistiken
```

## 🎯 Funktionsumfang

### ✅ Implementiert
- **Shortcode System:** `[ze_funnel id="123"]`
- **Question Types:** Text Input, Multiple Choice, Image/Icon Selection, Multi-Field Forms
- **Progress Tracking:** Fortschrittsbalken und Navigation
- **Bedingte Logik:** Frage-Verzweigungen basierend auf Antworten
- **Form Validation:** Client & Server-side Validierung
- **Analytics:** Event-Tracking und Conversion-Metriken
- **REST API:** Vollständige API für Frontend-Backend-Kommunikation
- **Admin Interface:** Funnel-Verwaltung, Status-Management, Bulk-Actions
- **Responsive Design:** Mobile-first CSS mit Dark Mode Support

### 🚧 Geplant
- Vue.js Funnel Builder (Drag & Drop)
- Visual Style Editor
- Email-Integration & Webhooks
- Advanced Analytics Dashboard
- Template System
- Multi-Language Support

## 🔧 Build System

### Vite Konfiguration
- **Frontend:** `vite.config.js` (Svelte)
- **Admin:** `vite.admin.config.js` (Vue.js)
- **Output:** Optimierte IIFE-Bundles für WordPress
- **Development:** HMR Support für schnelle Entwicklung

### Scripts
```json
{
  "dev": "vite",                    // Frontend Dev Server
  "dev:admin": "vite --config vite.admin.config.js", 
  "build": "vite build && vite build --config vite.admin.config.js",
  "watch": "vite build --watch",    // Watch Mode für PHP Dev
  "lint": "eslint . --ext .js,.svelte,.vue --fix",
  "format": "prettier --write ."
}
```

## 📝 Usage Examples

### Basic Shortcode
```
[ze_funnel id="1"]
```

### With Custom Anchor
```
[ze_funnel id="1" anchor_id="my-funnel"]
```

### PHP Integration
```php
// Programmatically create funnel
$db = ZeFunnel_Database::get_instance();
$funnel_id = $db->create_funnel([
    'name' => 'My Funnel',
    'description' => 'Lead qualification',
    'settings' => json_encode([
        'progressBar' => true,
        'allowBack' => true
    ])
]);
```

## 🎨 Styling & Themes

### CSS Custom Properties
```css
.ze-funnel {
  --ze-primary: #3b82f6;
  --ze-primary-hover: #2563eb;
  --ze-radius: 8px;
  --ze-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
```

### Theme Overrides
```css
/* Custom theme in your theme's style.css */
.ze-funnel {
  --ze-primary: #your-brand-color;
}
```

## 📈 Testing

### Sample Data
```sql
-- Run ze-funnel-sample-data.sql after activation
SOURCE ze-funnel-sample-data.sql;
```

### Test Funnels
1. **Lead Qualification Funnel** - Multi-step lead capture
2. **Product Interest Survey** - Customer feedback collection

## 🐛 Debugging

### Development Mode
```php
// wp-config.php
define('WP_DEBUG', true);
define('ZE_FUNNEL_DEBUG', true);
```

### Console Logs
- Frontend: `zeFunnelWP` global object
- Admin: `zeFunnelAdmin` global object
- Events: Browser DevTools Network Tab

## 🔒 Security

- **Nonce Validation:** Alle Admin-Actions & AJAX-Requests
- **Input Sanitization:** WordPress Sanitization Functions
- **SQL Injection:** Prepared Statements
- **Permission Checks:** `current_user_can()` für Admin-Features
- **CSRF Protection:** WordPress Nonce System

## 🌐 Browser Support

- **Modern Browsers:** Chrome 88+, Firefox 85+, Safari 14+
- **IE/Legacy:** Nicht unterstützt (ES6+ Required)
- **Mobile:** iOS Safari 14+, Android Chrome 88+

## 📦 File Structure

```
ze-funnel/
├── src/
│   ├── frontend/          # Svelte Components
│   ├── admin/            # Vue.js Admin Interface  
│   └── shared/           # Shared Utilities
├── php/
│   ├── includes/         # Core Classes
│   ├── api/             # REST API Endpoints
│   └── admin/           # WordPress Admin
├── dist/                # Built Assets
└── assets/              # Static Assets
```

## 🤝 Contributing

1. Fork the repository
2. Create feature branch: `git checkout -b feature/amazing-feature`
3. Follow coding standards: `npm run lint && npm run format`
4. Test thoroughly with sample data
5. Create Pull Request

## 📄 License

GPL-2.0+ - WordPress Plugin Standard License