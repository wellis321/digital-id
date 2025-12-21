# Development Guidelines

**⚠️ CRITICAL: These guidelines MUST be followed for all development work. Review this document before starting any new feature or making changes.**

## Table of Contents
1. [Icons and Visual Elements](#icons-and-visual-elements)
2. [Language and Spelling](#language-and-spelling)
3. [Code Standards](#code-standards)

---

## Icons and Visual Elements

### ❌ NEVER Use Emojis
**Emojis are strictly forbidden in this project.** They cause cross-platform compatibility issues, accessibility problems, and inconsistent rendering.

### ✅ ALWAYS Use Icon Libraries
We use **Font Awesome 6** (free version) for all icons. It is loaded globally in the header.

#### How to Use Icons

**Font Awesome Icons:**
```html
<!-- Solid icons (most common) -->
<i class="fas fa-lock"></i>
<i class="fas fa-mobile-alt"></i>
<i class="fas fa-bolt"></i>

<!-- Regular icons (outline style) -->
<i class="far fa-building"></i>

<!-- Brand icons -->
<i class="fab fa-microsoft"></i>
```

**Icon Sizing:**
```html
<!-- Use utility classes for sizing -->
<i class="fas fa-lock fa-2x"></i>        <!-- 2x size -->
<i class="fas fa-lock fa-3x"></i>        <!-- 3x size -->
<i class="fas fa-lock fa-lg"></i>        <!-- Large -->
```

**Icon Colouring:**
```html
<!-- Use CSS classes or inline styles -->
<i class="fas fa-check" style="color: #10b981;"></i>
```

#### Finding Icons
- Browse: https://fontawesome.com/icons
- Search by keyword (e.g., "lock", "mobile", "security")
- Use the free version icons only

#### Common Icon Mappings
- 🔒 → `fa-lock` or `fa-shield-alt`
- 📱 → `fa-mobile-alt` or `fa-mobile-screen-button`
- ⚡ → `fa-bolt` or `fa-zap`
- 🏢 → `fa-building` or `fa-building-columns`
- 🔗 → `fa-link` or `fa-chain`
- 📊 → `fa-chart-bar` or `fa-chart-line`
- 🚪 → `fa-door-open` or `fa-door-closed`
- 💾 → `fa-save` or `fa-database`
- 👥 → `fa-users` or `fa-user-group`
- ✓ → `fa-check` or `fa-check-circle`

---

## Language and Spelling

### ✅ UK English Only
**All user-facing text, documentation, comments, and code must use UK English spelling.**

#### Common UK vs US Spelling Differences

| UK English (Correct) | US English (Wrong) |
|---------------------|-------------------|
| organisation | organization |
| colour | color |
| centre | center |
| optimise | optimize |
| recognise | recognize |
| authorise | authorize |
| customise | customize |
| analyse | analyze |
| licence (noun) | license (noun) |
| licence (verb) | license (verb) |
| defence | defense |
| offence | offense |
| programme | program (except computer program) |
| behaviour | behavior |
| favour | favor |
| honour | honor |
| labour | labor |
| neighbour | neighbor |
| realise | realize |
| specialise | specialize |
| synchronise | synchronize |
| utilise | utilize |

#### Examples in Code

**Database/Code:**
```php
// ✅ CORRECT
$organisation = Organisation::find($id);
$organisationId = $user['organisation_id'];

// ❌ WRONG
$organization = Organization::find($id);
$organizationId = $user['organization_id'];
```

**User-Facing Text:**
```html
<!-- ✅ CORRECT -->
<p>Manage your organisation settings</p>
<p>Choose your favourite colour scheme</p>

<!-- ❌ WRONG -->
<p>Manage your organization settings</p>
<p>Choose your favorite color scheme</p>
```

**CSS Classes:**
- CSS properties use standard CSS (e.g., `color`, `center`) - this is fine
- But class names should use UK English: `organisation-card`, not `organization-card`

#### Exception: Technical Terms
Some technical terms are standardised and should remain as-is:
- "database" (not "databank")
- "server" (not "servant")
- "API" (standard abbreviation)
- CSS properties (`color`, `center`, etc.) - these are standard CSS

---

## Code Standards

### File Naming
- Use lowercase with underscores: `user_profile.php`
- Database tables: lowercase with underscores: `user_roles`
- CSS classes: lowercase with hyphens: `.user-profile`

### Database Naming
- Tables: plural, lowercase, underscores: `organisations`, `user_roles`
- Columns: lowercase, underscores: `organisation_id`, `email_verified`
- Foreign keys: `{table}_id` format: `organisation_id`, `user_id`

### PHP Standards
- Use PSR-12 coding standards where applicable
- Always use prepared statements for database queries
- Validate and sanitise all user input
- Use type hints where possible (PHP 7.4+)

### Security
- Always use CSRF protection on forms
- Never trust user input - always validate and sanitise
- Use password hashing (password_hash/password_verify)
- Implement proper session management

---

## Checklist Before Committing

Before committing any code, verify:

- [ ] No emojis used anywhere (search for emoji characters)
- [ ] All icons use Font Awesome classes
- [ ] All user-facing text uses UK English spelling
- [ ] Code comments use UK English spelling
- [ ] Variable names use UK English where applicable
- [ ] Database column names use UK English
- [ ] CSS class names use UK English (where applicable)

---

## Quick Reference

### Icon Library
- **Library:** Font Awesome 6 (Free)
- **CDN:** Loaded in `includes/header.php`
- **Documentation:** https://fontawesome.com/docs

### Language
- **Standard:** UK English
- **Dictionary Reference:** Oxford English Dictionary
- **Common Mistakes:** organisation (not organization), colour (not color), centre (not center)

---

## Questions?

If you're unsure about:
- Which icon to use → Check Font Awesome library
- Spelling → Use UK English dictionary
- Implementation → Refer to existing code patterns

**Remember: Consistency is key. When in doubt, check existing code for patterns.**

