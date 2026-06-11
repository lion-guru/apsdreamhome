# Add title attributes using Python with exact string replacement
import os

file_path = 'app/views/pages/home.php'

with open(file_path, 'r', encoding='utf-8') as f:
    content = f.read()

# Add title to loanAmount
content = content.replace(
    'max="50000000" step="100000" value="5000000" oninput="calcEMI()">',
    'max="50000000" step="100000" value="5000000" oninput="calcEMI()" title="Loan Amount">'
)

# Add title to interestRate
content = content.replace(
    'max="20" step="0.1" value="8.5" oninput="calcEMI()">',
    'max="20" step="0.1" value="8.5" oninput="calcEMI()" title="Interest Rate">'
)

# Add title to loanTenure
content = content.replace(
    'max="30" step="1" value="20" oninput="calcEMI()">',
    'max="30" step="1" value="20" oninput="calcEMI()" title="Loan Tenure Years">'
)

# Add title to invAmount
content = content.replace(
    'id="invAmount" onchange="calcGrowth()">',
    'id="invAmount" onchange="calcGrowth()" title="Investment Amount">'
)

# Add title to invYears
content = content.replace(
    'id="invYears" onchange="calcGrowth()">',
    'id="invYears" onchange="calcGrowth()" title="Time Period Years">'
)

with open(file_path, 'w', encoding='utf-8') as f:
    f.write(content)

print('Added title attributes')
