from flask import Flask, render_template, request, redirect, url_for, session, flash
import sqlite3
import os
from werkzeug.utils import secure_filename

app = Flask(__name__)
app.secret_key = 'supersecretkey'

UPLOAD_FOLDER = 'static/uploads'
ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg', 'gif'}
app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER

# Conexión a la base de datos
def get_db_connection():
    conn = sqlite3.connect('denuncias.db')
    conn.row_factory = sqlite3.Row
    return conn

# Crear la tabla de denuncias si no existe
def create_table():
    conn = get_db_connection()
    conn.execute('''
        CREATE TABLE IF NOT EXISTS denuncias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            email TEXT,
            mensaje TEXT NOT NULL,
            evidencia TEXT
        )
    ''')
    conn.commit()
    conn.close()

# Agregar la columna 'evidencia' si no existe
def add_column_evidencia():
    conn = get_db_connection()
    try:
        conn.execute('ALTER TABLE denuncias ADD COLUMN evidencia TEXT')
    except sqlite3.OperationalError:
        pass
    conn.commit()
    conn.close()

def allowed_file(filename):
    return '.' in filename and \
           filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/login', methods=['GET', 'POST'])
def login():
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password']
        
        if username == 'admin' and password == '123':
            session['logged_in'] = True
            return redirect(url_for('denuncias'))
        else:
            flash('Usuario o contraseña incorrectos', 'danger')
            return redirect(url_for('login'))
    
    return render_template('login.html')

@app.route('/logout', methods=['GET', 'POST'])
def logout():
    session.pop('logged_in', None)
    flash('Has cerrado sesión correctamente', 'success')
    return redirect(url_for('index'))

@app.route('/form', methods=['GET', 'POST'])
def form():
    if not session.get('privacidad_aceptada'):
        return redirect(url_for('aviso_privacidad', next='form'))

    if request.method == 'POST':
        nombre = request.form['nombre']
        email = request.form['email']
        mensaje = request.form['mensaje']
        file = request.files['evidencia']

        if file and allowed_file(file.filename):
            filename = secure_filename(file.filename)
            file.save(os.path.join(app.config['UPLOAD_FOLDER'], filename))
            evidencia = filename
        else:
            evidencia = None

        conn = get_db_connection()
        conn.execute('INSERT INTO denuncias (nombre, email, mensaje, evidencia) VALUES (?, ?, ?, ?)',
                     (nombre, email, mensaje, evidencia))
        conn.commit()
        conn.close()
        return redirect(url_for('index'))
    return render_template('form.html')

@app.route('/denuncias')
def denuncias():
    if not session.get('logged_in'):
        return redirect(url_for('login'))
    
    conn = get_db_connection()
    denuncias = conn.execute('SELECT * FROM denuncias').fetchall()
    conn.close()
    return render_template('denuncias.html', denuncias=denuncias)

@app.route('/quienes-somos')
def quienes_somos():
    return render_template('quienes-somos.html')

@app.route('/aviso-privacidad', methods=['GET', 'POST'])
def aviso_privacidad():
    if request.method == 'POST':
        session['privacidad_aceptada'] = True
        next_page = request.args.get('next', 'index')
        return redirect(url_for(next_page))
    return render_template('aviso_privacidad.html')

@app.route('/reportar-robo', methods=['GET', 'POST'])
def reportar_robo():
    if not session.get('privacidad_aceptada'):
        return redirect(url_for('aviso_privacidad', next='reportar_robo'))

    if request.method == 'POST':
        descripcion = request.form['descripcion']
        ubicacion = request.form['ubicacion']
        
        conn = get_db_connection()
        conn.execute('INSERT INTO denuncias (nombre, mensaje, evidencia) VALUES (?, ?, ?)',
                     ('Anónimo', f"Robo: {descripcion}, Ubicación: {ubicacion}", None))
        conn.commit()
        conn.close()
        flash('Robo reportado exitosamente', 'success')
        return redirect(url_for('index'))
    return render_template('reportar_robo.html')

if __name__ == '__main__':
    create_table()
    add_column_evidencia()
    if not os.path.exists(UPLOAD_FOLDER):
        os.makedirs(UPLOAD_FOLDER)
    app.run(debug=True)

