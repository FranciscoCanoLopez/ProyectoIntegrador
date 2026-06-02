-- Cambiar la contraseña del usuario administrativo 'sa' por seguridad
ALTER LOGIN sa WITH PASSWORD = 'ContaduriaSecure2026!';
ALTER LOGIN sa ENABLE;
GO

-- Crear un usuario de respaldo personalizado con permisos de administrador
CREATE LOGIN admin_contable WITH PASSWORD = 'ContaduriaSecure2026!', DEFAULT_DATABASE = master;
ALTER SERVER ROLE sysadmin ADD MEMBER admin_contable;
GO