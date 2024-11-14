# Proyecto de Tienda en Línea de Códigos de Juegos 
Este proyecto es una tienda en línea dedicada a la venta de códigos de juegos digitales y puntos para diversas plataformas. La aplicación permite a los usuarios registrarse, iniciar sesión, ver productos disponibles, añadir productos a un carrito de compras y consultar detalles de cada producto. Además, cuenta con un sistema de administración donde los usuarios pueden gestionar productos y realizar compras.

## Tecnologías Usadas 
El proyecto está desarrollado utilizando una combinación de tecnologías y herramientas tanto en el front-end como en el back-end. A continuación se detallan las principales:

HTML5: Para la estructura básica de las páginas.
CSS3: Estilos y personalización de la interfaz. Se utilizan clases y estilos específicos para mejorar la experiencia visual del usuario.
Bootstrap 5: Framework CSS que facilita el diseño responsivo y la disposición de elementos. Utilizado para formularios, botones y estructuras de tarjeta.
JavaScript: Se usa principalmente para la funcionalidad interactiva en la página y para manejar eventos.
Google Maps API: Integrada para autocompletar la dirección en el registro de usuarios, permitiendo una entrada más precisa de direcciones.
Back-End
PHP: Lenguaje de programación principal en el lado del servidor. PHP maneja la lógica del negocio, como el inicio de sesión, registro de usuarios y consultas de productos.
MySQL: Base de datos relacional para almacenar toda la información de usuarios, productos, carrito de compras e historial de compras.
Otros Recursos
Font Awesome: Librería de iconos utilizada para íconos visuales, como el ícono del carrito de compras.
XAMPP: Herramienta de servidor local para ejecutar PHP y MySQL en desarrollo.

## Estructura del Proyecto 
index.php: Página principal donde se muestran los productos disponibles.
loginReg.html: Página de registro e inicio de sesión para los usuarios.
procesarReg.php: Archivo que maneja la lógica de registro de usuarios e inserta los datos en la base de datos.
detalle_producto.php: Página que muestra los detalles de un producto específico cuando el usuario selecciona "Más información".
carrito.php: Página que muestra los productos añadidos al carrito de compras.

## Base de Datos 🗄
La base de datos incluye las siguientes tablas:

Usuarios: Almacena la información de los usuarios, como nombre de usuario, correo electrónico, contraseña (encriptada), fecha de nacimiento, dirección postal y si es administrador.
Productos: Almacena los productos disponibles en la tienda, incluyendo nombre, descripción, foto, precio y cantidad en inventario.
Carrito_Compras: Guarda los productos que los usuarios han añadido a su carrito de compras.
Historial_Compras: Registro histórico de compras realizadas por los usuarios.

## Funcionalidades Principales 
Registro e inicio de sesión de usuarios: Los usuarios pueden registrarse y acceder a su cuenta para ver productos, añadir al carrito y hacer compras.
Consulta de productos: La página principal muestra todos los productos disponibles. Cada producto cuenta con una imagen, descripción breve y precio.
Carrito de compras: Los usuarios pueden añadir productos a su carrito para posteriormente realizar la compra.
Detalles del producto: Al hacer clic en "Más información" en cualquier producto, se redirige a una página de detalles que muestra información completa del producto seleccionado.
Google Maps API: Al registrar una dirección en el proceso de registro, se utiliza la API de Google Maps para autocompletar, mejorando la precisión de la dirección ingresada.
Instalación y Configuración 
Clona este repositorio en tu servidor local o entorno de desarrollo: