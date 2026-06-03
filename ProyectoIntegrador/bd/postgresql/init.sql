-- ============================================================================
-- SCRIPT DE INICIALIZACIÓN CON TRIGGERS DE INMUTABILIDAD - MÓDULO 2
-- MOTOR: PostgreSQL 15+
-- ============================================================================

CREATE TABLE IF NOT EXISTS audit_logs (
    id SERIAL PRIMARY KEY,
    modulo_origen VARCHAR(50) NOT NULL,
    usuario VARCHAR(100) NOT NULL,
    accion VARCHAR(50) NOT NULL,
    tabla_afectada VARCHAR(100),
    registro_id VARCHAR(50),
    valores_anteriores JSONB,
    valores_nuevos JSONB,
    direccion_ip VARCHAR(45),
    fecha_registro TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_audit_modulo ON audit_logs(modulo_origen);
CREATE INDEX IF NOT EXISTS idx_audit_usuario ON audit_logs(usuario);
CREATE INDEX IF NOT EXISTS idx_audit_fecha ON audit_logs(fecha_registro);

-- ============================================================================
-- 1. FUNCIÓN DEL TRIGGER: Bloquear UPDATE y DELETE en la bitácora
-- ============================================================================
CREATE OR REPLACE FUNCTION proteger_auditoria()
RETURNS TRIGGER AS $$
BEGIN
    -- RAISE EXCEPTION cancela la transacción y envía un mensaje de error
    RAISE EXCEPTION 'LOG DE AUDITORÍA INMUTABLE: No está permitido modificar ni eliminar registros de la bitácora.';
    RETURN NULL; 
END;
$$ LANGUAGE plpgsql;

-- ============================================================================
-- 2. ASIGNACIÓN DEL TRIGGER A LA TABLA
-- ============================================================================
CREATE OR REPLACE TRIGGER trg_proteger_auditoria
BEFORE UPDATE OR DELETE ON audit_logs
FOR EACH ROW
EXECUTE FUNCTION proteger_auditoria();

-- ============================================================================
-- 3. DATOS SEMILLA (Idénticos a los anteriores)
-- ============================================================================
INSERT INTO audit_logs (modulo_origen, usuario, accion, tabla_afectada, registro_id, valores_nuevos, direccion_ip)
VALUES 
(
    'Modulo_2_PHP', 
    'sistema_auditoria', 
    'INITIALIZATION', 
    'database', 
    '0', 
    '{"status": "Base de datos de auditoria inicializada correctamente", "version": "1.0"}'::jsonb, 
    '127.0.0.1'
),
(
    'Modulo_1_DotNet', 
    'admin', 
    'LOGIN', 
    'Users', 
    '1', 
    '{"resultado": "Login exitoso", "rol": "Admin", "departamento": "TI"}'::jsonb, 
    '192.168.1.45'
),
(
    'Modulo_1_DotNet', 
    'gerente', 
    'CREATE_DOCUMENT', 
    'Documents', 
    '104', 
    '{"titulo": "Manual de Calidad V2.pdf", "tipo": "Procedimiento", "estado": "Pendiente_Revision"}'::jsonb, 
    '192.168.1.48'
),
(
    'Modulo_3_NodeJS', 
    'revisor', 
    'SEARCH_DOCUMENT', 
    'Elasticsearch_Index', 
    'N/A', 
    '{"query_busqueda": "Manual de Calidad", "filtros_aplicados": {"departamento": "Calidad"}}'::jsonb, 
    '192.168.1.60'
);