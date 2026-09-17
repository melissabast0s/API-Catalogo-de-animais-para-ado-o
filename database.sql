Banco de dados MySQL:
Nome do banco de dados: petcontrol_api

 Tabela: animais 
id INT AUTO_INCREMENT PK 
nome VARCHAR(100) NOT NULL 
especie VARCHAR(50) NOT NULL 
raca VARCHAR(50) NOT NULL
idade DECIMAL(4,1) NOT NULL
status ENUM('Disponível', 'Adotado', 'Tratamento') DEFAULT 'Disponível'
foto VARCHAR(255) NULL 
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP()