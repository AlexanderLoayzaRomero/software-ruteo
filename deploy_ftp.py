import os
import sys
import ftplib

sys.stdout.reconfigure(encoding='utf-8')

FTP_HOST = '82.25.72.113'
FTP_USER = 'u612525723'
REMOTE_BASE = '/domains/oracleperu.org/public_html/wp-content/plugins/wp-ruteo'
LOCAL_BASE = os.path.join(os.path.dirname(__file__), 'wp-content', 'plugins', 'wp-ruteo')

def upload_dir(ftp, local_dir, remote_dir):
    try:
        ftp.cwd(remote_dir)
    except ftplib.error_perm:
        print(f"Creando directorio remoto: {remote_dir}")
        ftp.mkd(remote_dir)
        ftp.cwd(remote_dir)

    for item in os.listdir(local_dir):
        if '' in item or '\\' in item or item.startswith('.'):
            continue
            
        local_path = os.path.join(local_dir, item)
        remote_path = f"{remote_dir}/{item}"
        
        if os.path.isdir(local_path):
            upload_dir(ftp, local_path, remote_path)
            ftp.cwd(remote_dir)
        else:
            with open(local_path, 'rb') as f:
                print(f"Subiendo: {item} -> {remote_path}")
                ftp.storbinary(f'STOR {item}', f)

def main():
    password = sys.argv[1] if len(sys.argv) > 1 else os.getenv('FTP_PASSWORD')
    if not password:
        print("Uso: python deploy_ftp.py <clave_ftp>")
        sys.exit(1)

    print(f"Conectando a {FTP_HOST} como {FTP_USER}...")
    try:
        ftp = ftplib.FTP()
        ftp.connect(FTP_HOST, 21, timeout=15)
        ftp.login(FTP_USER, password)
        ftp.set_pasv(True)
        print("Conexion FTP exitosa (modo pasivo).")
        
        upload_dir(ftp, LOCAL_BASE, REMOTE_BASE)
        
        # Subir mu-plugins (hostinger-smtp.php)
        mu_local = os.path.join(os.path.dirname(__file__), 'wp-content', 'mu-plugins')
        mu_remote = '/domains/oracleperu.org/public_html/wp-content/mu-plugins'
        if os.path.exists(mu_local):
            upload_dir(ftp, mu_local, mu_remote)
            
        ftp.quit()
        print("¡Despliegue FTP completado con exito en /domains/oracleperu.org/public_html!")
    except Exception as e:
        print(f"Error durante la transferencia FTP: {e}")

if __name__ == '__main__':
    main()
