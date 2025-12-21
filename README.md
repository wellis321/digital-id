# Digital ID Application

A secure digital ID application for social care providers, built with PHP and SQL. This application provides secure, verifiable employee identification with support for visual verification, QR codes, and NFC.

## ⚠️ Important: Development Standards

**Before starting any development work, please read:**
- [`DEVELOPMENT_GUIDELINES.md`](DEVELOPMENT_GUIDELINES.md) - **MANDATORY READING**
- `.cursorrules` - Cursor AI development rules

**Key Requirements:**
- ❌ **NEVER use emojis** - Always use Font Awesome 6 icons
- ✅ **ALWAYS use UK English spelling** - Never American English
- 📖 See guidelines document for complete standards

## Features

- **Multi-tenant Organisation System**: Organisation-based authentication and access control
- **Digital ID Cards**: Secure, verifiable employee identification cards
- **Progressive Security Levels**: 
  - Visual verification (photo + details)
  - QR code verification (time-limited tokens)
  - NFC verification (contactless)
- **Online Verification**: Public verification page for identity confirmation
- **Audit Trail**: Complete logging of all verification attempts
- **Data Portability**: JSON export/import for employee ID data
- **Optional Microsoft Entra/365 Integration**: SSO and employee synchronisation

## Requirements

- PHP 7.4+ (8.0+ recommended)
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx)
- Composer (for dependencies, optional)

## Installation

1. **Clone or download the repository**

2. **Set up the database**
   ```sql
   CREATE DATABASE digital_id CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Run database migrations**
   - First, run the shared authentication schema:
     ```sql
     source shared-auth/migrations/core_schema.sql
     ```
   - Then, run the application schema:
     ```sql
     source sql/schema.sql
     ```

4. **Configure environment**
   - Copy `.env.example` to `.env`
   - Update database credentials and application settings

5. **Set up file permissions**
   ```bash
   chmod -R 755 uploads/
   ```

6. **Configure web server**
   - Point document root to the `public/` directory
   - Or configure URL rewriting if using project root

## Configuration

### Environment Variables

Create a `.env` file in the project root:

```env
APP_ENV=development
APP_NAME=Digital ID
APP_URL=http://localhost

DB_HOST=localhost
DB_NAME=digital_id
DB_USER=root
DB_PASS=

MAIL_FROM=noreply@digitalid.com
MAIL_REPLY_TO=support@digitalid.com

# Optional: Microsoft Entra/365
ENTRA_CLIENT_SECRET=your_client_secret_here
```

### Database Configuration

The application uses the shared authentication package for core authentication. Ensure the database is properly configured in your `.env` file.

## Usage

### Creating Employees

1. Register users through the registration page
2. As an organisation admin, go to "Manage Employees"
3. Link users to employee records with unique employee references
4. Employees can then view their digital ID cards

### Verification

- **Visual**: Display ID card and compare photo/details
- **QR Code**: Scan QR code on ID card for online verification
- **NFC**: Activate NFC on device and tap for verification
- **Manual Lookup**: Use verification page to search by employee reference

### Microsoft Entra/365 Integration (Optional)

1. Register application in Azure AD
2. Configure redirect URI: `{APP_URL}/entra-login.php`
3. Grant API permissions: `User.Read`, `openid`, `profile`, `email`
4. Set `ENTRA_CLIENT_SECRET` in environment
5. Enable integration in admin settings with Tenant ID and Client ID

## Project Structure

```
digital-id/
├── config/              # Configuration files
├── includes/            # Header/footer templates
├── public/              # Public-facing files
│   ├── admin/          # Admin pages
│   ├── api/            # API endpoints
│   └── assets/         # CSS, images, etc.
├── shared-auth/        # Shared authentication package
├── src/                # Application source code
│   ├── classes/        # Core classes
│   └── models/         # Data models
├── sql/                # Database schema
└── uploads/            # User uploads (photos)
```

## Security Considerations

- All tokens are cryptographically random
- Tokens expire after 5 minutes (configurable)
- ID cards can be revoked
- Complete audit trail of verifications
- CSRF protection on all forms
- SQL injection prevention (prepared statements)
- XSS prevention (output escaping)

## License

MIT

## Support

For issues and questions, please contact your system administrator.

