# API Endpoint: Obtener Usuarios Pendientes

## 📋 Endpoint de Usuarios Pendientes

### 🔐 Autenticación Requerida
- **Header**: `Authorization: Bearer {token}`
- **Roles permitidos**: `admin` o `babalawo`

---

## Obtener Usuarios Pendientes

```http
GET /admin/users/pending
```

**Descripción**: Obtiene la lista de usuarios pendientes de aprobación ordenados por fecha de creación (más recientes primero)

**Input**: Ninguno

**Output**:
```json
// Éxito (200)
[
  {
    "id": 1,
    "email": "usuario@example.com",
    "full_name": "Juan Pérez",
    "role": "santero",
    "status": "pending",
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T10:30:00Z",
    "notes": "Información adicional del usuario"
  },
  {
    "id": 2,
    "email": "maria@example.com",
    "full_name": "María García",
    "role": "iyanifa",
    "status": "pending",
    "created_at": "2024-01-14T15:20:00Z",
    "updated_at": "2024-01-14T15:20:00Z",
    "notes": null
  }
]

// Error (401) - Token inválido o faltante
{
  "error": "Token inválido"
}

// Error (403) - Usuario sin permisos
{
  "error": "No autorizado"
}
```

---

## 🔧 Ejemplo de Uso con JavaScript/Fetch

```javascript
// Función para obtener usuarios pendientes
async function getPendingUsers() {
  const token = localStorage.getItem('authToken');
  
  try {
    const response = await fetch('/admin/users/pending', {
      method: 'GET',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      }
    });
    
    const data = await response.json();
    
    if (response.ok) {
      console.log('Usuarios pendientes:', data);
      return data;
    } else {
      console.error('Error:', data.error);
      throw new Error(data.error);
    }
  } catch (error) {
    console.error('Error de red:', error);
    throw error;
  }
}

// Uso
getPendingUsers()
  .then(users => {
    // Procesar lista de usuarios pendientes
    users.forEach(user => {
      console.log(`${user.full_name} (${user.email}) - ${user.role}`);
    });
  })
  .catch(error => {
    // Manejar error (mostrar mensaje al usuario, redirigir al login, etc.)
  });
```

---

## 📝 Notas Importantes

- El endpoint retorna un array vacío `[]` si no hay usuarios pendientes
- Los usuarios se ordenan por `created_at` descendente (más recientes primero)
- El campo `password_hash` se excluye automáticamente por seguridad
- El campo `notes` puede ser `null` si no hay notas adicionales 