import os

# Cấu hình lọc
IGNORED_DIRS = {'.git', 'node_modules', 'vendor', 'fonts'}
BINARY_EXTS = {'.png', '.jpg', '.jpeg', '.gif', '.ico', '.pdf'}
CODE_EXTS = {'.php', '.js', '.sql', '.html', '.css'}

def scan_project(root_folder):
    project_summary = {
        'structure': "", # Chuỗi cây thư mục gửi cho Claude
        'code_content': "", # Nội dung code quan trọng gửi cho Claude
        'stats': {'images': 0, 'code_files': 0, 'lines_of_code': 0} # Số liệu cho Excel
    }
    
    for root, dirs, files in os.walk(root_folder):
        # 1. Loại bỏ folder rác
        dirs[:] = [d for d in dirs if d not in IGNORED_DIRS]
        
        level = root.replace(root_folder, '').count(os.sep)
        indent = ' ' * 4 * (level)
        project_summary['structure'] += f"{indent}{os.path.basename(root)}/\n"
        
        for f in files:
            file_path = os.path.join(root, f)
            ext = os.path.splitext(f)[1].lower()
            
            project_summary['structure'] += f"{indent}    {f}\n"
            
            # 2. Xử lý file Binary (Ảnh)
            if ext in BINARY_EXTS:
                project_summary['stats']['images'] += 1
                
            # 3. Xử lý file Code (Chỉ lấy nội dung file quan trọng)
            elif ext in CODE_EXTS:
                project_summary['stats']['code_files'] += 1
                try:
                    with open(file_path, 'r', encoding='utf-8', errors='ignore') as file:
                        content = file.read()
                        project_summary['stats']['lines_of_code'] += len(content.splitlines())
                        
                        # Chỉ ghép nội dung SQL và PHP vào prompt gửi AI
                        if ext in ['.sql', '.php']: 
                            project_summary['code_content'] += f"\n--- FILE: {f} ---\n{content}\n"
                except Exception as e:
                    print(f"Lỗi đọc file {f}: {e}")

    return project_summary

# Sử dụng
# data = scan_project('C:/xampp/htdocs/my_project')
# Gửi data['code_content'] cho Claude để vẽ sơ đồ
# Dùng data['stats'] để điền vào Excel