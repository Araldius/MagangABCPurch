from pathlib import Path

files = [
    Path('resources/views/dashboard/admin.blade.php'),
    Path('resources/views/dashboard/user.blade.php'),
]

for f in files:
    text = f.read_text(encoding='utf-8')
    replacements = [
        ('\u0010hp\\Carbon', '\\Carbon'),
        ('hp\\Carbon', '\\Carbon'),
        ('hp\\Carbon\\Carbon', '\\Carbon\\Carbon'),
    ]
    new = text
    for old, new_value in replacements:
        new = new.replace(old, new_value)
    if new != text:
        f.write_text(new, encoding='utf-8')
        print(f'Updated {f}')
    else:
        print(f'No change {f}')
