#!/usr/bin/env python3
import subprocess, os, re

MD_PATH = "/home/rizki/.gemini/antigravity/brain/6ceb4624-bdc2-44e7-8197-3d3f8662809e/manual_user_nexora_digital.md"
HTML_PATH = "/home/rizki/BotTeleStore/nexora-digital/manual_nexora_digital.html"
PDF_PATH  = "/home/rizki/BotTeleStore/nexora-digital/Manual_User_Nexora_Digital.pdf"

with open(MD_PATH, "r") as f:
    md = f.read()

def md_to_html(text):
    # Code blocks
    text = re.sub(r'```[a-z]*\n(.*?)```', lambda m: f'<pre><code>{m.group(1).strip()}</code></pre>', text, flags=re.DOTALL)
    # Inline code
    text = re.sub(r'`([^`]+)`', r'<code>\1</code>', text)
    # Tables
    def convert_table(block):
        lines = [l.strip() for l in block.strip().splitlines() if l.strip()]
        rows = [l for l in lines if not re.match(r'^[\|\-\s:]+$', l)]
        html = '<table>'
        for i, row in enumerate(rows):
            cells = [c.strip() for c in row.strip('|').split('|')]
            tag = 'th' if i == 0 else 'td'
            html += '<tr>' + ''.join(f'<{tag}>{c}</{tag}>' for c in cells) + '</tr>'
        return html + '</table>'
    text = re.sub(r'(\|.+\|[\s\S]*?)(?=\n\n|\n#|\Z)', lambda m: convert_table(m.group(1)), text)
    # Blockquotes
    text = re.sub(r'(?m)^> ?(.+)', r'<blockquote>\1</blockquote>', text)
    # Headings
    text = re.sub(r'^#### (.+)$', r'<h4>\1</h4>', text, flags=re.MULTILINE)
    text = re.sub(r'^### (.+)$', r'<h3>\1</h3>', text, flags=re.MULTILINE)
    text = re.sub(r'^## (.+)$', r'<h2>\1</h2>', text, flags=re.MULTILINE)
    text = re.sub(r'^# (.+)$', r'<h1>\1</h1>', text, flags=re.MULTILINE)
    # Bold/italic
    text = re.sub(r'\*\*(.+?)\*\*', r'<strong>\1</strong>', text)
    text = re.sub(r'\*(.+?)\*', r'<em>\1</em>', text)
    # Horizontal rule
    text = re.sub(r'^---+$', '<hr>', text, flags=re.MULTILINE)
    # Ordered lists
    text = re.sub(r'(?m)((?:^\d+\. .+\n?)+)', lambda m: '<ol>' + re.sub(r'^\d+\. (.+)$', r'<li>\1</li>', m.group(1), flags=re.MULTILINE) + '</ol>', text)
    # Unordered lists
    text = re.sub(r'(?m)((?:^[-*] .+\n?)+)', lambda m: '<ul>' + re.sub(r'^[-*] (.+)$', r'<li>\1</li>', m.group(1), flags=re.MULTILINE) + '</ul>', text)
    # Paragraphs
    paras = text.split('\n\n')
    out = []
    for p in paras:
        p = p.strip()
        if not p: continue
        if re.match(r'^<(h[1-6]|ul|ol|table|pre|hr|blockquote)', p):
            out.append(p)
        else:
            out.append(f'<p>{p}</p>')
    return '\n'.join(out)

body = md_to_html(md)

html = f"""<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Manual User — Nexora Digital</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
  * {{ box-sizing: border-box; margin: 0; padding: 0; }}
  body {{ font-family: 'Inter', Arial, sans-serif; font-size: 13px; color: #1a1a2e; line-height: 1.7; padding: 40px 60px; }}
  h1 {{ font-size: 26px; color: #1a1a2e; border-bottom: 3px solid #4f46e5; padding-bottom: 10px; margin: 30px 0 8px; }}
  h2 {{ font-size: 19px; color: #4f46e5; border-left: 4px solid #4f46e5; padding-left: 10px; margin: 28px 0 10px; page-break-after: avoid; }}
  h3 {{ font-size: 15px; color: #1a1a2e; margin: 20px 0 8px; }}
  h4 {{ font-size: 13px; color: #555; margin: 14px 0 6px; }}
  p {{ margin: 8px 0; }}
  strong {{ color: #1a1a2e; }}
  code {{ background: #f0f0f7; border: 1px solid #ddd; border-radius: 3px; padding: 1px 5px; font-family: 'Courier New', monospace; font-size: 12px; color: #c7254e; }}
  pre {{ background: #1e1e2e; color: #cdd6f4; border-radius: 6px; padding: 14px; margin: 12px 0; overflow-x: auto; }}
  pre code {{ background: none; border: none; color: #cdd6f4; font-size: 12px; }}
  table {{ width: 100%; border-collapse: collapse; margin: 12px 0; font-size: 12px; }}
  th {{ background: #4f46e5; color: white; padding: 8px 12px; text-align: left; }}
  td {{ border: 1px solid #ddd; padding: 7px 12px; }}
  tr:nth-child(even) td {{ background: #f8f8ff; }}
  blockquote {{ background: #eef2ff; border-left: 4px solid #4f46e5; padding: 10px 16px; margin: 10px 0; border-radius: 0 6px 6px 0; color: #333; }}
  ul, ol {{ padding-left: 22px; margin: 8px 0; }}
  li {{ margin: 4px 0; }}
  hr {{ border: none; border-top: 1px solid #e0e0f0; margin: 20px 0; }}
  .cover {{ text-align: center; padding: 80px 0 60px; page-break-after: always; }}
  .cover h1 {{ border: none; font-size: 32px; color: #4f46e5; }}
  .cover .subtitle {{ font-size: 16px; color: #666; margin-top: 8px; }}
  .cover .meta {{ margin-top: 40px; font-size: 13px; color: #888; }}
  .cover .badge {{ display: inline-block; background: #4f46e5; color: white; padding: 6px 18px; border-radius: 20px; font-size: 13px; margin-top: 16px; }}
</style>
</head>
<body>
<div class="cover">
  <h1>⚡ Nexora Digital</h1>
  <div class="subtitle">Manual User — Sistem Toko Digital Otomatis via Telegram Bot</div>
  <div class="badge">Versi 1.0</div>
  <div class="meta">Terakhir Diperbarui: Juli 2026</div>
</div>
{body}
</body>
</html>"""

with open(HTML_PATH, "w") as f:
    f.write(html)

print(f"HTML dibuat: {HTML_PATH}")

result = subprocess.run([
    "google-chrome", "--headless", "--no-sandbox", "--disable-gpu",
    "--disable-dev-shm-usage",
    f"--print-to-pdf={PDF_PATH}",
    "--print-to-pdf-no-header",
    f"file://{HTML_PATH}"
], capture_output=True, text=True, timeout=60)

if result.returncode == 0 and os.path.exists(PDF_PATH):
    size_kb = os.path.getsize(PDF_PATH) // 1024
    print(f"PDF berhasil dibuat: {PDF_PATH} ({size_kb} KB)")
else:
    print("STDERR:", result.stderr[-500:])
    raise RuntimeError("Chrome gagal membuat PDF")
