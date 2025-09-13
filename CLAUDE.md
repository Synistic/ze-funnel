# WordPress Conversational Funnel Plugin

## Projektvision

Entwicklung eines modularen WordPress-Plugins für **conversational forms** bzw. **qualification funnels** - interaktive, schrittweise Befragungen die wie ein natürliches Gespräch ablaufen und Leads durch personalisierte Fragesequenzen qualifizieren.

## Analyse bestehender Plugins

### dd-funnel (Entwicklungsversion - strukturell problematisch)
**Status:** Sourcecode vorhanden, aber schlecht strukturiert
- **Frontend:** jQuery-basiert mit Webpack Build-System
- **Architektur:** Monolithische `FunnelSlider.js` Klasse (1300+ Zeilen)
- **Datenstruktur:** Statische JSON-Dateien mit verschachtelten Slide-Konfigurationen
- **Hauptprobleme:**
  - Keine Modularisierung, schwer wartbar
  - Hardgecodete deutsche Texte
  - Komplexe bedingte Logik für Navigation
  - Manuelle DOM-Manipulation
  - Keine komponentenbasierte Architektur

### wp-funnel (Produktionsversion - professionelle Struktur)
**Status:** Fertiges Plugin ohne Sourcecode, nur installierte Version verfügbar
- **Frontend:** Vue.js-basiert (erkennbar an Template-Syntax mit router-view, v-if, etc.)
- **Backend:** WordPress Custom Post Type "funnel" + REST API
- **Features:**
  - Post Meta JSON-Konfiguration
  - Email-Integration und Webhooks  
  - Logging-System ("funnel_log")
  - Lizenz-Validierung
  - Export-Funktionalität
  - REST Endpoint: `/wp-funnel/v1/submit`

**Fazit:** wp-funnel zeigt die richtige architektonische Richtung, aber wir bauen eine verbesserte Version mit mehr Flexibilität.

## Kernfunktionalitäten

### Frontend (User Experience)
**Interaktive Fragesequenzen mit verschiedenen Input-Typen:**
- **Bildauswahl** (imageselection) - User wählt aus Bildoptionen
- **Icon-Auswahl** (icon_selection) - User wählt aus Icon-Optionen  
- **Text-Auswahl** (text_selection) - Multiple-Choice Textoptionen
- **Text-Eingaben** (text_input) - Freitext-Felder
- **Multi-Input** - Kombinierte Eingabemasken
- **Opt-in Formulare** - Finale Kontaktdaten-Erfassung

**Navigation & UX:**
- Progress-Bar zeigt Fortschritt
- "Zurück"-Button für Navigation
- Smooth Animationen zwischen Fragen
- Responsive Design
- Thank-You-Seite nach Abschluss

**Bedingte Logik:**
- Je nach Antwort werden verschiedene Follow-up-Fragen gezeigt
- Verzweigungslogik basierend auf User-Input
- Dynamische Fragesequenzen

### Backend (Admin Interface)
**Funnel-Management:**
- Visueller Funnel-Editor zum Erstellen der Fragesequenzen
- Drag & Drop Interface für Fragen-Anordnung
- Bedingte Verzweigungen konfigurieren
- Preview-Modus für Tests

**Integration & Automation:**
- E-Mail-Templates konfigurieren
- Webhook-Integration für externe Services
- Lead-Export (CSV, Excel)
- Analytics und Tracking
- Shortcode-Generator für WordPress-Integration

**Styling-System:**
- Standard-Design mit vorgefertigter CSS
- Admin-Interface für CSS/Style-Overrides
- Live-Preview der Style-Änderungen
- Responsive Design-Optionen

## Modulare Architektur-Anforderungen

### Flexibles Input-System
**Kernprinzip:** Trennung von Frage-Content und Darstellungs-Art
- Eine Frage kann durch verschiedene Input-Typen beantwortet werden
- **Beispiel:** Frage "Wie alt sind Sie?" → Antwort-Optionen:
  - Slider (18-99)
  - Dropdown-Liste
  - Freitext-Input
  - Altersbereich-Buttons (18-25, 26-35, etc.)

### Datenmodell-Konzept
```
Frage → {
  id: unique_identifier,
  content: "Frage-Text",
  description: "Optionale Erklärung",
  input_types: [array_of_possible_input_methods],
  validation_rules: validation_config,
  conditional_logic: next_question_rules,
  styling: custom_css_overrides
}

Antwort → {
  question_id: reference,
  user_input: actual_answer,
  input_method: how_answered,
  timestamp: when_answered
}
```

### Komponenten-Architektur
**Frontend-Komponenten:**
- Question-Renderer (dynamisch je nach Input-Typ)
- Progress-Component
- Navigation-Component  
- Animation-Wrapper
- Form-Validator

**Backend-Komponenten:**
- Funnel-Builder
- Question-Editor
- Logic-Builder (bedingte Verzweigungen)
- Style-Editor
- Analytics-Dashboard

## Styling-System

### Standard-Design
- Vorgefertigte `style.css` als Basis-Design
- Professionelles, modernes Aussehen out-of-the-box
- Mobile-first, responsive Layout
- Accessibility-konform (WCAG)

### Customization-Optionen
**Admin-Interface für:**
- Farben (Primary, Secondary, Accent)
- Typografie (Schriftarten, Größen, Abstände)  
- Layout-Optionen (Breiten, Abstände, Ausrichtung)
- Button-Styles (Formen, Hover-Effekte)
- Animationen (Ein/Aus, Geschwindigkeit)
- Custom CSS-Eingabefeld für erweiterte Anpassungen

**Live-Preview:**
- Echtzeit-Vorschau der Style-Änderungen
- Device-Simulation (Desktop, Tablet, Mobile)
- Vor/Nach-Vergleich

## User Journey

### Admin-Workflow
1. **Plugin installieren** und aktivieren
2. **Neuen Funnel erstellen** über Admin-Menü
3. **Fragen definieren** mit Drag & Drop Builder
4. **Input-Typen auswählen** für jede Frage
5. **Bedingte Logik konfigurieren** (wenn A dann B)
6. **Styling anpassen** über Visual Editor
7. **E-Mail/Webhook-Integration** einrichten
8. **Shortcode generieren** und in Seite einbetten
9. **Testing** über Preview-Modus
10. **Go Live** und Analytics überwachen

### End-User-Workflow
1. **Landingpage besuchen** mit eingebettetem Funnel
2. **Startfrage** wird präsentiert
3. **Schrittweise antworten** mit verschiedenen Input-Methoden
4. **Personalisierte Fragesequenz** basierend auf Antworten
5. **Finale Kontaktdaten-Eingabe** (Opt-in)
6. **Thank-You-Seite** mit nächsten Schritten
7. **Automated Follow-up** via E-Mail/Webhook

## Technische Anforderungen

### WordPress-Integration
- Kompatibilität: WordPress 5.0+, PHP 7.4+
- Eigenes Admin-Interface (NICHT Custom Post Type)
- REST API für Frontend-Backend-Kommunikation
- Shortcode-System für einfache Einbettung
- Multisite-kompatibel

### Performance
- Lazy Loading von Assets
- Minimierte CSS/JS-Bundles
- Caching-Integration
- CDN-ready

### Sicherheit
- Nonce-Validierung für alle Admin-Actions
- Input-Sanitization
- SQL-Injection-Schutz
- CSRF-Protection

### Erweiterbarkeit
- Hook-System für Entwickler
- Plugin-API für Extensions
- Template-Override-System
- Event-System für Tracking

## Tech Stack

### Frontend-Technologie
**Svelte** - Compile-time optimiertes Framework
- **Vorteile:**
  - Kleinste Bundle-Sizes (50-80% kleiner als Vue/React)
  - Keine Runtime-Overhead durch Compile-time-Optimierung
  - Reactive Bindings perfekt für Formulare und dynamische UIs
  - Scoped CSS-in-Component ohne zusätzliches Setup
  - Weniger Konflikt-Potential mit anderen WordPress-Plugins
  - Einfachere Syntax näher an nativem HTML/CSS/JS

### Build-System
**Vite** - Moderne Build-Pipeline
- **Vorteile:**
  - Extrem schnelle Entwicklung durch ES-Module Hot Reload
  - Optimierte Production-Builds
  - Excellent Tree-Shaking für minimale Bundle-Sizes
  - Native TypeScript-Support
  - Plugin-Ökosystem für WordPress-spezifische Optimierungen

### Admin-Interface
**Shadcn-Vue** - Component Library für WordPress-Backend
- **Verwendung:** WordPress Admin-Panels und Dashboard-Interfaces
- **Vorteile:**
  - Professionelle UI-Komponenten out-of-the-box
  - Konsistente Design-Sprache
  - Accessibility-konform
  - Customizable und themeable

### Code-Qualität
**Prettier + ESLint** - Automatisierte Code-Formatierung und -Validierung
- **Prettier:** Konsistente Code-Formatierung
- **ESLint:** JavaScript/TypeScript Linting und Best Practices
- **Integration:** Pre-commit Hooks für automatische Validierung

### Projektstruktur
```
ze-funnel/
├── src/
│   ├── frontend/          # Svelte Components für User-Facing Funnels
│   │   ├── components/    # Wiederverwendbare UI-Komponenten
│   │   ├── stores/        # Svelte Stores für State Management
│   │   └── utils/         # Helper Functions
│   ├── admin/            # Admin-Interface mit Shadcn-Vue
│   │   ├── pages/        # Admin-Seiten (Funnel Builder, etc.)
│   │   ├── components/   # Admin-spezifische Komponenten
│   │   └── composables/  # Vue Composables für Admin-Logic
│   └── shared/           # Geteilte Utilities zwischen Frontend/Admin
├── php/                  # WordPress Backend (PHP)
│   ├── includes/         # Core Plugin-Logik
│   ├── api/             # REST API Endpoints
│   └── admin/           # WordPress Admin-Integration
├── assets/              # Statische Assets (CSS, Images)
└── dist/                # Built Assets (generiert von Vite)
```

### Build-Pipeline
**Development:**
```bash
npm run dev          # Vite Dev Server mit HMR
npm run dev:admin    # Admin-Interface Development
npm run lint         # ESLint + Prettier Check
```

**Production:**
```bash
npm run build        # Optimierte Production Builds
npm run preview      # Production Build Preview
```

### WordPress-Integration
**PHP-Framework:** Vanilla WordPress (keine zusätzlichen PHP-Dependencies)
- **REST API:** `/wp-json/ze-funnel/v1/` Namespace
- **Admin-Pages:** Native WordPress Admin-Integration
- **Shortcodes:** `[ze_funnel id="123"]` für Frontend-Einbettung
- **Hooks:** WordPress-Hook-System für Erweiterbarkeit

### Datenbank
**Custom Tables** für optimale Performance:
```sql
ze_funnels          # Funnel-Konfigurationen
ze_questions        # Fragen-Pool
ze_submissions      # User-Antworten
ze_analytics        # Tracking-Daten
```

### Asset-Management
- **CSS:** PostCSS mit Autoprefixer und CSS-Minification
- **JS:** Tree-shaking für minimale Bundle-Sizes
- **Lazy Loading:** Dynamische Imports für bessere Performance
- **CDN-Ready:** Optimierte Asset-URLs für CDN-Integration

## Datenschutz & Compliance
- DSGVO-konforme Datenverarbeitung
- Opt-in/Opt-out-Mechanismen
- Datenexport/Löschung auf Anfrage
- Cookie-Management-Integration
- Audit-Log für Admin-Aktionen

## Competitive Advantages
- **Modulare Architektur:** Flexibler als bestehende Lösungen
- **Visual Builder:** Keine Programmierkenntnisse erforderlich
- **Style-System:** Vollständige Design-Kontrolle
- **Performance:** Moderne Frontend-Architektur
- **Integration:** Native WordPress-Integration ohne externe Dependencies