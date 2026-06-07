// src/node-service/index.js
const express = require('express');
const mongoose = require('mongoose');
const cors = require('cors');

const app = express();
const PORT = process.env.PORT || 3000;

// Middlewares globales
app.use(cors());
app.use(express.json());

// --- CONEXIÓN AUTOMÁTICA CON CREDENCIALES A MONGODB ---
// Toma la URI inyectada por tu docker-compose, o usa la de respaldo exacta
const MONGO_URI = process.env.MONGO_URI || 'mongodb://admin:secretmongo@mongodb-db:27017/metadatos?authSource=admin';

mongoose.connect(MONGO_URI)
    .then(() => console.log('🌿 [Módulo 3] Conectado exitosamente a MongoDB (Colección: metadatos)'))
    .catch(err => console.error('❌ [Módulo 3] Error crítico de conexión en MongoDB:', err));

// --- DEFINICIÓN DEL ESQUEMA Y MODELO NO SQL (Mongoose BSON) ---
const LogSchema = new mongoose.Schema({
    id_referencia: { type: Number, required: true, index: true },
    modulo_origen: { type: String, required: true, trim: true },
    usuario: { type: String, required: true, trim: true },
    accion: { type: String, required: true },
    direccion_ip: { type: String },
    etiquetas: [{ type: String, lowercase: true, trim: true }], // Etiquetas ANSI y de búsqueda rápida
    valores_nuevos: { type: mongoose.Schema.Types.Mixed, default: {} }, // Almacenamiento libre JSONB -> BSON
    fecha_registro: { type: Date, default: Date.now }
});

// Índice compuesto de texto completo para búsquedas globales tipo Google
LogSchema.index({ modulo_origen: 'text', usuario: 'text', accion: 'text', etiquetas: 'text' });

const Log = mongoose.model('Log', LogSchema);

// --- ENDPOINT 1: SINCRONIZACIÓN ASÍNCRONA DESDE EL MÓDULO 2 ---
app.post('/api/notificaciones/sincronizar', async (req, res) => {
    try {
        const { id, modulo_origen, usuario, accion, direccion_ip, valores_nuevos } = req.body;

        if (!id || !modulo_origen || !usuario || !accion) {
            return res.status(400).json({ status: 'error', message: 'Faltan campos obligatorios para la indexación.' });
        }

        // Algoritmo de generación automática de etiquetas ANSI inteligentes basadas en metadatos
        const etiquetasGeneradas = [
            modulo_origen,
            usuario,
            accion.split(' ')[0] // Extrae acciones primarias como: "INSERT", "UPDATE", "DELETE"
        ];

        // Si el payload JSONB relacional tiene llaves internas, las indexamos automáticamente como etiquetas de metadatos
        if (valores_nuevos && typeof valores_nuevos === 'object') {
            Object.keys(valores_nuevos).forEach(key => {
                etiquetasGeneradas.push(key);
            });
        }

        const nuevoLogNoSQL = new Log({
            id_referencia: id,
            modulo_origen,
            usuario,
            accion,
            direccion_ip,
            etiquetas: etiquetasGeneradas,
            valores_nuevos
        });

        await nuevoLogNoSQL.save();

        res.status(201).json({ 
            status: 'success', 
            message: 'Metadatos indexados correctamente en MongoDB para Búsqueda Inteligente sin afectar la DB transaccional.' 
        });

    } catch (error) {
        res.status(500).json({ status: 'error', message: 'Error interno en indexación: ' + error.message });
    }
});

// --- ENDPOINT 2: MOTOR DE BÚSQUEDA INTELIGENTE POR TEXTO O FILTRO DE ETIQUETAS ---
app.get('/api/notificaciones/buscar', async (req, res) => {
    try {
        const { query, etiqueta } = req.query;
        let filtro = {};

        // Si se filtra por una etiqueta ANSI específica
        if (etiqueta) {
            filtro.etiquetas = etiqueta.toLowerCase();
        }

        // Si se realiza una búsqueda global de texto (búsqueda inteligente difusa)
        if (query) {
            filtro.$text = { $search: query };
        }

        const resultados = await Log.find(filtro).sort({ fecha_registro: -1 });

        res.json({
            status: 'success',
            modulo: 'Módulo 3 - Servicio de Búsqueda Inteligente NoSQL',
            total_resultados: resultados.length,
            data: resultados
        });

    } catch (error) {
        res.status(500).json({ status: 'error', message: 'Error en el motor de búsqueda: ' + error.message });
    }
});

// Encendido oficial del Microservicio
app.listen(PORT, () => {
    console.log(`🚀 [Módulo 3] Microservicio Node.js activo en puerto ${PORT}`);
});