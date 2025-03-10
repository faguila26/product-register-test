<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulario de Producto</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <h1>Formulario de Producto</h1>
        <form id="productoForm" action="register.php" method="POST">
            <div class="form-group">
                <div class="input-container">
                    <label for="codigo">Código</label>
                    <input type="text" id="codigo" name="codigo" value="" required class="input-field">
                    <div id="codigo-error" style="color: red; display: none;"></div>
                </div>
                <div class="input-container">
                    <label for="nombre">Nombre</label>
                    <input type="text" id="nombre" name="nombre" value="" required class="input-field">
                    <div id="nombre-error" style="color: red; display: none;"></div>
                </div>
            </div>
            <div class="form-group">
                <div class="input-container">
                    <label for="bodega">Bodega</label>
                    <select id="bodega" name="bodega" required class="select-field">
                        <option value=""></option>
                    </select>
                    <div id="bodega-error" style="display: none; color: red;">Error</div>
                </div>
                <div class="input-container">
                    <label for="sucursal">Sucursal</label>
                    <select id="sucursal" name="sucursal" required class="select-field">
                        <option value=""></option>
                    </select>
                    <div id="sucursal-error" style="display: none; color: red;">Error</div>
                </div>
            </div>

            <div class="form-group">
                <div class="input-container">
                    <label for="moneda">Moneda</label>
                    <select id="moneda" name="moneda" required class="select-field">
                    <option value=""></option>
                    </select>
                    <div id="moneda-error" style="display: none; color: red;">Error</div>
                </div>
                <div class="input-container">
                    <label for="precio">Precio</label>
                    <input type="text" id="precio" name="precio" value="" required class="input-field">
                    <div id="precio-error" style="display: none; color: red;"></div>
                </div>
            </div>
            <div id="material-container" style="display: flex; flex-direction: row; gap: 100px; flex-wrap: wrap;"></div>
            <br>
            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="4" required class="textarea-field"></textarea>
                <div id="descripcion-error" style="display: none; color: red;"></div>
            </div>
            <div class="form-group">
                <button type="submit">Guardar Producto</button>
            </div>
        </form>
        <script src="script.js"></script> <!-- Vincula tu archivo JavaScript -->
    </div>
</body>
</html>
