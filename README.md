Bienvenido a The Drop Vinyls esperamos te guste mucho nuestro sitio, encuentra los vinyls de tus artistas favoritos con el mejor precio :)

💿 The Drop Vinyls - E-commerce de Vinilos

The Drop Vinyls es una plataforma web completa diseñada para melómanos que buscan la experiencia del ritual del vinilo en la era digital. El sistema permite la gestión de inventario, navegación de productos, carrito de compras y generación de facturas imprimibles.

⭐ Características Principales:

👤 Para Usuarios (Clientes) 

● Gestión de Perfil: Los usuarios pueden actualizar su nombre, correo y dirección desde un modal intuitivo.

● Carrito Dinámico: Sistema para agregar, visualizar y eliminar productos antes de finalizar la compra.

● Historial de Compras: Panel personalizado para revisar pedidos anteriores con montos y fechas.

● Facturación: Generación automática de facturas en formato profesional con opción de impresión directa.

🖥️ Para Administradores 

● Panel de Control: Interfaz para gestionar el inventario de la tienda.

● CRUD de Productos: Capacidad para crear, editar y eliminar vinilos, artistas, precios y stock.

●Gestión de Categorías: Organización de productos por géneros musicales (Pop, Rock, Electrónica, etc.).


🛠️ Requisitos Previos
Para poder instalar y ejecutar este proyecto, asegúrate de tener instalado en tu equipo:

● Docker y Docker Compose

● Git

📥 Instalación Paso a Paso: 
1. Clona el repositorio en tu máquina local:
git clone https://github.com/Hernan416/ecommerce-laury-hernan.git
cd ecommerce-laury-hernan

2. Configura las variables de entorno necesarias (copia el archivo de ejemplo si aplica):
cp .env.example .env

🖱️ Cómo Levantar el Sistema con Docker
El sistema cuenta con soporte completo para contenedores, permitiendo levantar la aplicación y su base de datos de forma automática sin pasos manuales adicionales:
docker compose up --build

Una vez ejecutado el comando, podrás acceder a la aplicación desde tu navegador o cliente HTTP.

📚 Documentación de la API
El sistema expone endpoints seguros bajo el prefijo /api/v1/. Los principales recursos disponibles son:

| Verbo HTTP | Ruta     | Descripción                |
| :-------- | :------- | :------------------------- |
| `GET` | `/api/v1/vinilos` | Lista el catálogo público de vinilos disponibles en stock. |
| `POST` | `/api/v1/pedidos` | Procesa el checkout, valida inventario y genera una nueva orden de compra. |
| `PUT` | `/api/v1/usuario/{id}` | Actualiza los datos personales y la dirección de envío del cliente autenticado. |
| `DELETE` | `/api/v1/pedidos/{id}` | Cancela un pedido específico y libera el inventario retenido.|

💾 Ejecución de Pruebas Unitarias
El proyecto incluye un conjunto de pruebas unitarias con validaciones y asserts significativos. Para ejecutarlas, utiliza el comando correspondiente a tu entorno:
Ejecutar según el framework (ej. php artisan test / pytest / npm test





