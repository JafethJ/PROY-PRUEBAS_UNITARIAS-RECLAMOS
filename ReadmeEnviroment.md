# Importar la base de datos del proyecto
# Tener CodeIgniter y Composer instalado
# crear un archivo .env en la raiz del proyecto y pegar lo de abajo



#--------------------------------------------------------------------
# Example Environment Configuration file
#
# This file can be used as a starting point for your own
# custom .env files, and contains most of the possible settings
# available in a default install.
#
# By default, all of the settings are commented out. If you want
# to override the setting, you must un-comment it by removing the '#'
# at the beginning of the line.
#--------------------------------------------------------------------

#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------

CI_ENVIRONMENT = development

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------

# app.baseURL = ''
# -----MODIFICADO POR JAFETH J------------
app_baseURL = 'http://localhost/reclamosSugerencias/public'

# -----MODIFICADO POR JAFETH J------------


# If you have trouble with `.`, you could also use `_`.
# app_baseURL = ''
# app.forceGlobalSecureRequests = false
# app.CSPEnabled = false

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------

database.default.hostname = 127.0.0.1
#oooooooo
#database.default.hostname = localhost

database.default.database = semestral_dsvii
database.default.username = d72025
database.default.password = 1234
database.default.DBDriver = MySQLi
# database.default.DBPrefix =
database.default.port = 3306

# If you use MySQLi as tests, first update the values of Config\Database::$tests.
database.tests.hostname = localhost
database.tests.database = ci4_test
database.tests.username = d72025
database.tests.password = 1234
database.tests.DBDriver = MySQLi
# database.tests.DBPrefix =
# database.tests.charset = utf8mb4
# database.tests.DBCollat = utf8mb4_general_ci
database.tests.port = 3306

email.fromEmail = "reclamossugerencias31@gmail.com"
email.fromName = "Reclamos y Sugerencias DSVII"
email.protocol = "smtp"
email.SMTPHost = "smtp.gmail.com"
email.SMTPUser = "reclamossugerencias31@gmail.com"
email.SMTPPass = "vctx ufhc usqo xvrk"  # ¡La que generaste con Gmail!
email.SMTPPort = 587
email.SMTPTimeout = 60
email.SMTPCrypto = "tls"
email.mailType = "html"

#--------------------------------------------------------------------
# ENCRYPTION
#--------------------------------------------------------------------

# encryption.key =

#--------------------------------------------------------------------
# SESSION
#--------------------------------------------------------------------

# session.driver = 'CodeIgniter\Session\Handlers\FileHandler'
# session.savePath = null

#--------------------------------------------------------------------
# LOGGER
#--------------------------------------------------------------------

# logger.threshold = 4
