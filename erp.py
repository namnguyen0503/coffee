import os
import datetime

# --- CẤU HÌNH LỌC ---
IGNORED_DIRS = {'.git', 'node_modules', 'vendor', 'fonts', '.idea', '.vscode', '__pycache__'}
BINARY_EXTS = {'.png', '.jpg', '.jpeg', '.gif', '.ico', '.pdf', '.zip', '.exe', '.woff', '.woff2'}
# Các file code Logic quan trọng cần AI đọc (PHP, SQL)
LOGIC_EXTS = {'.php', '.sql', '.py'} 
# Các file giao diện chỉ cần đếm, không cần đọc nội dung
VIEW_EXTS = {'.html', '.css', '.js'} 

def scan_and_export(root_folder, output_file="project_scan_output.txt"):
    """
    Quét folder dự án và xuất ra báo cáo TXT được format chuẩn.
    """
    print(f"Dang quet du an: {root_folder}...")
    
    # Khởi tạo dữ liệu
    summary = {
        'project_name': os.path.basename(os.path.normpath(root_folder)),
        'scan_time': datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        'structure': "",
        'code_content': "",
        'logs': [],
        'stats': {
            'total_files': 0,
            'php_files': 0,
            'view_files': 0, # HTML/CSS/JS
            'sql_files': 0,
            'images': 0,
            'logic_lines': 0
        }
    }

    # Bắt đầu duyệt cây thư mục
    for root, dirs, files in os.walk(root_folder):
        # 1. Loại bỏ folder rác (Sửa trực tiếp list dirs để không duyệt sâu vào)
        dirs[:] = [d for d in dirs if d not in IGNORED_DIRS]
        
        # Tạo cây thư mục (Structure)
        level = root.replace(root_folder, '').count(os.sep)
        indent = '    ' * level
        folder_name = os.path.basename(root)
        if level == 0: folder_name = summary['project_name'] # Root folder
        summary['structure'] += f"{indent}{folder_name}/\n"
        
        for f in files:
            file_path = os.path.join(root, f)
            ext = os.path.splitext(f)[1].lower()
            summary['stats']['total_files'] += 1
            
            # Ghi vào cây thư mục
            summary['structure'] += f"{indent}    {f}\n"

            # 2. Xử lý File Binary/Ảnh
            if ext in BINARY_EXTS:
                summary['stats']['images'] += 1
                summary['logs'].append(f"[SKIPPED BINARY] {os.path.join(root, f)}")
            
            # 3. Xử lý File View (HTML/CSS/JS) - Chỉ đếm
            elif ext in VIEW_EXTS:
                summary['stats']['view_files'] += 1
                # Không đọc nội dung để tiết kiệm token
            
            # 4. Xử lý File Logic (PHP/SQL) - Đọc nội dung cho AI
            elif ext in LOGIC_EXTS:
                if ext == '.php': summary['stats']['php_files'] += 1
                if ext == '.sql': summary['stats']['sql_files'] += 1
                
                try:
                    with open(file_path, 'r', encoding='utf-8', errors='ignore') as file:
                        content = file.read()
                        line_count = len(content.splitlines())
                        summary['stats']['logic_lines'] += line_count
                        
                        # Format nội dung để gửi AI
                        rel_path = os.path.relpath(file_path, root_folder)
                        summary['code_content'] += f"\n---------------- START FILE: {rel_path} ----------------\n"
                        summary['code_content'] += content
                        summary['code_content'] += f"\n---------------- END FILE ----------------\n"
                except Exception as e:
                    summary['logs'].append(f"[ERROR READING] {f}: {str(e)}")
            
            else:
                summary['logs'].append(f"[IGNORED TYPE] {f}")

    # --- GHI RA FILE TXT ---
    write_report_file(output_file, summary)
    print(f"Hoan tat! Da xuat file: {output_file}")


def write_report_file(filename, data):
    """Hàm phụ trợ để ghi file TXT theo format đẹp"""
    
    with open(filename, 'w', encoding='utf-8') as f:
        # PHẦN 1: THỐNG KÊ
        f.write("="*64 + "\n")
        f.write("PHẦN 1: THỐNG KÊ TỔNG QUAN (DÙNG CHO EXCEL REPORT)\n")
        f.write("="*64 + "\n")
        f.write(f"Project Name: {data['project_name']}\n")
        f.write(f"Scan Date:    {data['scan_time']}\n")
        f.write(f"Total Files:  {data['stats']['total_files']}\n")
        f.write("-" * 32 + "\n")
        f.write("[File Counts by Type]\n")
        f.write(f"- PHP Files (Logic):    {data['stats']['php_files']}\n")
        f.write(f"- HTML/CSS/JS (View):   {data['stats']['view_files']}\n")
        f.write(f"- SQL (Database):       {data['stats']['sql_files']}\n")
        f.write(f"- Images/Assets:        {data['stats']['images']} (Đã bỏ qua nội dung)\n")
        f.write("-" * 32 + "\n")
        f.write("[Code Volume]\n")
        f.write(f"- Total Lines of Logic: {data['stats']['logic_lines']} lines\n\n")

        # PHẦN 2: CẤU TRÚC
        f.write("="*64 + "\n")
        f.write("PHẦN 2: CẤU TRÚC THƯ MỤC (PROJECT TREE)\n")
        f.write("="*64 + "\n")
        f.write(data['structure'])
        f.write("\n")

        # PHẦN 3: NỘI DUNG CODE
        f.write("="*64 + "\n")
        f.write("PHẦN 3: NỘI DUNG CODE CỐT LÕI (CONTEXT CHO CLAUDE)\n")
        f.write("="*64 + "\n")
        f.write(">>> CHỈ DẪN CHO AI: Dưới đây là logic xử lý và CSDL.\n")
        f.write(">>> Hãy bỏ qua CSS/HTML. Hãy dùng thông tin này vẽ DFD và ERD.\n")
        f.write(data['code_content'])
        f.write("\n")

        # PHẦN 4: LOGS
        f.write("="*64 + "\n")
        f.write("PHẦN 4: LOG CÁC FILE ĐÃ BỎ QUA HOẶC LỖI (DEBUG)\n")
        f.write("="*64 + "\n")
        for log in data['logs']:
            f.write(log + "\n")

# --- CHẠY CHƯƠNG TRÌNH ---
if __name__ == "__main__":
    # Thay đổi đường dẫn này thành folder dự án của bạn
    PROJECT_PATH = r"C:\xampp\htdocs\coffee" 
    
    # Tên file xuất ra
    OUTPUT_FILE = "ket_qua_scan.txt"
    
    scan_and_export(PROJECT_PATH, OUTPUT_FILE)