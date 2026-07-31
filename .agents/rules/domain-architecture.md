---
trigger: always_on
---

# Domain & Routing
- This is the SIREKA ASSI app for ship repair information management. 
- The target server is Rocky Linux 9; strictly use Linux conventions (forward slashes, case sensitivity) for paths and files.
- Do not use Laravel resource controllers/routes; manually declare CRUD endpoints in `routes/web.php`.

# Authentication & Authorization
- Authentication relies strictly on `employee_id`, not email. 
- Standard users must have a unique 9-digit `employee_id`. The `admin` is the only user with a unique string username instead of an ID.
- Post-login, user context (`employee_id`, `employee_name`, `employee_position`) is stored in the session and read by the UI.
- Authorization combines Spatie Permissions with a custom role/position layer (seeders populate expected roles).

# Exports
- All exported files/documents must use the Tahoma font, located at `public/assets/fonts/tahoma.ttf`.