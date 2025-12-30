# Audio-Based Threat Detection Configuration
import os
from pathlib import Path

# Base paths
BASE_DIR = Path(__file__).resolve().parent.parent
DATASET_DIR = BASE_DIR / "Non Speech Dataset"
MODELS_DIR = BASE_DIR / "models" / "saved"
LOGS_DIR = BASE_DIR / "logs"

# Audio Configuration
class AudioConfig:
    SAMPLE_RATE = 16000  # 16kHz for speech recognition
    CHUNK_DURATION = 2.0  # 2 seconds per chunk
    OVERLAP = 0.5  # 50% overlap
    N_MFCC = 40  # Number of MFCC coefficients
    N_FFT = 2048
    HOP_LENGTH = 512
    N_MELS = 128
    FMAX = 8000

# Model Configuration
class ModelConfig:
    # Non-Speech Model
    NON_SPEECH_CLASSES = ['crying', 'screaming', 'shouting', 'glass_breaking', 'normal']
    NON_SPEECH_MODEL_PATH = MODELS_DIR / "non_speech_threat_model.h5"
    
    # Speech Model
    SPEECH_THREAT_MODEL_PATH = MODELS_DIR / "speech_threat_model.h5"
    
    # Training Parameters
    BATCH_SIZE = 32
    EPOCHS = 50
    LEARNING_RATE = 0.001
    VALIDATION_SPLIT = 0.2
    
    # Detection Thresholds
    NON_SPEECH_THRESHOLD = 0.7
    SPEECH_THREAT_THRESHOLD = 0.6
    
    # Latency Target (seconds)
    MAX_LATENCY = 3.0

# Threat Keywords for Speech Detection
class ThreatKeywords:
    # ============================================================================
    # ENGLISH KEYWORDS
    # ============================================================================
    
    # Direct Violence/Killing/Harming (Enhanced)
    ENGLISH_THREATS = [
        # Direct Violence/Killing/Harming
        "i'll hurt you", "i will hurt you", "kill", "murder", "die", "death", "perish",
        "attack", "shoot", "bomb", "fight", "beat you", "punch", "destroy",
        "harm", "violence", "assault", "stab", "choke", "strangle", "slaughter",
        "massacre", "execute", "maim", "injure", "torture", "rape", "kidnap",
        "suicide", "self-harm", "hang", "drown", "poison", "eliminate", "slit throat",
        "put an end to you", "gonna make you bleed", "i hate my life", "i want to end it",
        "decapitate", "behead", "dismember", "mutilate", "crucify", "lynch",
        "annihilate", "obliterate", "exterminate", "liquidate", "assassinate",
        "butcher", "finish you", "end you", "take you out", "waste you",
        "break your bones", "crack your skull", "bash your head", "smash your face",
        
        # Weapons & Related Terms (Enhanced)
        "gun", "weapon", "knife", "blade", "sword", "firearm", "rifle", 
        "pistol", "shotgun", "ammunition", "bullet", "grenade", "explosive", 
        "device", "trigger", "detonate", "razor", "ax", "machete", "taser",
        "semtex", "c4", "ak-47", "uzi", "glock", "sniper", "scope",
        "molotov", "IED", "pipe bomb", "pressure cooker", "detonator",
        "carbine", "revolver", "assault rifle", "submachine gun", "caliber",
        "magazine", "chamber", "silencer", "suppressor", "crossbow", "arrow",
        "spear", "hatchet", "chainsaw", "bat", "club", "brass knuckles",
        
        # Intent/Planning/Specific Threats (Enhanced)
        "threat", "threatening", "intention to harm", "plan to attack", "gonna get you", 
        "come for you", "watch your back", "payback", "revenge", "doomsday", 
        "hostage", "terror", "jihad", "cult", "trap", "ambush", "blow up", 
        "burn down", "destroy all", "im coming for you", "you'll regret this",
        "just wait", "i know where you live", "i have your address",
        "counting down", "final warning", "last chance", "say goodbye",
        "prepare to die", "your days are numbered", "you're dead",
        "marked for death", "hunting you", "tracking you", "found you",
        "see you soon", "waiting for you", "you're next", "on my list",
        
        # Distress/Emergency/Seeking Help (Enhanced)
        "help", "danger", "emergency", "in trouble", "call police", "911", 
        "fire", "medic", "ambulance", "save me", "distress", "panic", "critical",
        "need help", "im scared", "someone call", "please help", "sos",
        "trapped", "cant breathe", "bleeding", "overdose", "hurting myself",
        "going to jump", "about to", "losing consciousness", "chest pain"
   
        # General Bullying Actions
        "bully", "bullying", "harass", "harassment", "torment", "tease", "pick on",
        "intimidate", "ostracize", "exclude", "shame", "mock", "gossip",
        "spread rumors", "troll", "doxxing", "cyberbully", "stalk", "creep",
        "make fun of", "call you names", "talk bad about you",
        "humiliate", "degrade", "belittle", "ridicule", "taunt", "jeer",
        "persecute", "victimize", "target", "single out", "gang up",
        
        # Commands/Insults related to Submission
        "shut up", "get lost", "nobody likes you", "go away", "worthless", 
        "freak", "weirdo", "loser", "nobody cares", "ugly", "fat", "stupid",
        "go kill yourself", "i wish you were dead", "pathetic",
        "everyone hates you", "you have no friends", "unwanted", "rejected",
        "disgusting", "gross", "repulsive", "mistake", "disappointment",
        "waste of space", "should never have been born", "burden",
        
        # Specific Online Bullying Terms
        "report you", "ban you", "dox", "screenshot", "leak", "exposed", "fake",
        "catfish", "anonymous", "hater", "troll account", "ratio", "canceled",
        "swatting", "brigading", "dogpiling", "pile on", "mass report"
    ]
    
    # Profanity/Slurs (Significantly Enhanced)
    PROFANITY_ENGLISH = [
        # Basic Profanity
        "damn", "hell", "bastard", "idiot", "stupid", "ass", "bitch", 
        "crap", "fuck", "shit", "motherfucker", "cock", "pussy", 
        "cunt", "arsehole", "wanker", "moron", "loser", "jerk", 
        "douchebag", "prick", "slut", "whore", "tard", "scum", 
        "trash", "suck", "sucks", "go to hell", "piss off", "bollocks", 
        "shite", "wuss", "pussycat", "snowflake", "retard", "gobshite",
        
        # Additional Common Profanity
        "dickhead", "asshole", "shithead", "fuckface", "twat", "tosser",
        "bellend", "knobhead", "pillock", "muppet", "numpty", "git",
        "bugger", "sod", "bloodclaat", "bumbaclot", "puta", "pendejo",
        "hijo de puta", "cabrón", "pinche", "culero", "verga",
        
        # Body-Shaming & Appearance-Based Insults
        "fatso", "fatty", "whale", "pig", "cow", "beast", "ogre",
        "midget", "dwarf", "giant", "anorexic", "skeleton", "twig",
        "four eyes", "pizza face", "crater face", "butterface",
        
        # Intellectual/Mental Insults
        "dumbass", "dumbfuck", "stupid fuck", "braindead", "brainless",
        "imbecile", "cretin", "dunce", "dimwit", "halfwit", "nitwit",
        "mentally deficient", "special ed", "window licker",
        
        # Sexual/Gendered Insults
        "man whore", "thot", "hoe", "skank", "tramp", "hooker",
        "prostitute", "escort", "simp", "cuck", "beta", "soyboy",
        "karen", "becky", "chad", "incel", "femoid", "roastie",
        
        # Racial/Ethnic/Identity Slurs (Handle with extreme caution)
        "nigger", "nigga", "coon", "darkie", "colored", "spook",
        "faggot", "fag", "homo", "queer", "tranny", "dyke", "lesbo",
        "kike", "yid", "heeb", "chink", "gook", "nip", "jap",
        "spic", "beaner", "wetback", "greaser", "towelhead", "raghead",
        "paki", "curry muncher", "gypsy", "pikey", "white trash", "cracker",
        "redneck", "hillbilly", "backward", "savage", "primitive"
    ]

    # ============================================================================
    # SINHALA KEYWORDS (SIGNIFICANTLY ENHANCED)
    # ============================================================================
    
    # Direct Violence/Killing/Harming (Massively Enhanced)
    SINHALA_THREATS = [
        # Direct Violence/Killing/Harming
        "මරනවා", "ගහනවා", "මරන්න", "කපනවා", "පහර", "සටන", 
        "පොලු", "තලනවා", "අතවර", "පහරදීම", "ඝාතනය", "තුවාල", 
        "වින්දනය", "විස", "දැවිල්ල", "පිළිස්සීම", "අල්ලා", 
        "අල්ලාගෙන", "බැඳලා", "පන්නනවා", "ගෙල සිර", "දුක්", 
        "නැතිකරනවා", "සමූලඝාතනය", "මංකොල්ල", "බලහත්කාර",
        "තියෙනවා", "පලිගන්නවා", "පලි",
        
        # Additional Violence Terms
        "තට්ටු කරනවා", "කඩනවා", "ගසනවා", "පගනවා", "තලනවා",
        "පයින් ගහනවා", "අත ගහනවා", "හිස කඩනවා", "මූණ ඉරනවා",
        "ඇට කඩනවා", "මස් කඩනවා", "බඩ ඇනුම", "මරා දමනවා",
        "මරල දානවා", "බටලා මරනවා", "ගොඩ දානවා", "තල්ලු කරනවා",
        "ඇද දමනවා", "පහර ගහනවා", "පන්නල මරනවා", "බීපු මරනවා",
        "උන්නම් කරනවා", "වැස්සෙන් අරිනවා", "රුපියලට නැතිකරනවා",
        "මිනිස්සු ගලවනවා", "කපාගෙන මරනවා", "තඩි බොවනවා",
        "කුරුණෑගල දානවා", "වහලම", "කොන්දේ කඩනවා", "දත පෙරළනවා",
        
        # Weapons & Actions (Enhanced)
        "වෙඩි", "පිස්තෝලය", "බෝම්බය", "මරුගුල", "කඩු", 
        "පිහිය", "තුවක්කුව", "ආයුධය", "පතරොම", "විසබීජ", 
        "විස", "පිපිරීම", "පුපුරවනවා", "ගිනි", "වෙඩි තියනවා",
        "පිහි පහර",
        
        # Additional Weapon Terms
        "රයිෆලය", "පිස්තෝල", "ශොට් ගන්", "බුලට්", "බෝම්බ",
        "ග්‍රිනේඩ්", "විහිදුම්", "පිපි බෝම්බ", "අත් බෝම්බ",
        "පොල්කිරි තෙල්", "තීක්ෂ්ණ ආයුධ", "වෙඩි උණ්ඩ", "වෙඩිමරු",
        "කඩුව", "කතරය", "චීන පිහිය", "බෝම්බ පුපුරනවා",
        "වෙඩි තියලා මරනවා", "ගිනි තියනවා", "පුපුරන ද්‍රව්‍ය",
        
        # Intent/Planning/Specific Threats (Enhanced)
        "බය", "තර්ජනය", "අන්තරාය", "අවදානම", "පලි", 
        "කුමන්ත්‍රණය", "ඇටවුම්", "බිහිසුණු", "ත්‍රස්ත", 
        "ප්‍රහාරය", "හමුදා", "පොලිසියට කියන්න", "උඹව ඉවරයි",
        "බලාගෙන", "මම එනවා", "ගෙදරට එනවා",
        
        # Additional Threat Terms
        "උඹව බලාගන්න", "පලියට අරමුණ", "පලිගන්න එනවා",
        "පලි සකස් කරනවා", "සැලසුම් තියනවා", "හොඳ පාඩමක් දෙනවා",
        "ඔයා ඉවරයි", "ඔයාගේ කාලය ආවා", "ඔයා මැරෙනවා",
        "තව දවස් ටිකයි", "ගණන් දානවා", "බලාගෙන ඉන්න",
        "උඹ අස්සේ කරගන්නම්", "මාවත බලන් හිටපන්", "දැන් බලපන්",
        "එන්නම්", "තෝ ගෙදර මං දන්නවා", "ලිපිනය දන්නවා",
        "තෝ කොහේ යන්නේ", "තෝව හොයලා බලනවා", "හම්බවෙනකම්",
        
        # Distress/Emergency/Seeking Help (Enhanced)
        "උදව්", "අවශ්‍යයි", "ගලවගන්න", "බේරගන්න", "ගිලන්", 
        "ගිලන්රථ", "අමාරුවේ", "අසනීප", "මරණ", "ජීවිතේ එපා වෙලා",
        
        # Additional Distress Terms
        "කරුණාකර උදව් කරන්න", "මට අනතුරක්", "මට බයයි", "හදිස්සි",
        "මම අන්තරාවේ", "මම සිරවිලා", "මට හුස්ම ගන්න බැහැ",
        "මම මැරෙනවා", "මට හිත් කැක්කුම", "මම පනින්න යනවා",
        "මම රුධිරය යනවා", "මට අසනීපයි", "මට වද දෙනවා",
        "මට ඇඩ ගහනවා", "මට මරනවා", "මාව බේරගන්න",
        "මට පොලිස් එක අවශ්‍යයි", "119", "110", "118"
  
        # General Bullying/Exclusion
        "හිරිහැර", "තර්ජනය", "අඩන්තේට්ටම්", "ගේම්", "වද", 
        "රැවටීම", "රහස්", "කේලම්", "බොරු", "ප්‍රසිද්ධ",
        "කොන්", "විහිළු", "උසුළු", "විකාර", "කළු", "පට්ට",
        
        # Additional Bullying Terms
        "හිරිහැර කරනවා", "කරදර කරනවා", "තර්ජනය කරනවා",
        "බය ගන්වනවා", "හිනා වෙනවා", "විහිළු කරනවා",
        "උපහාස කරනවා", "නින්දා කරනවා", "අපහාස කරනවා",
        "නම් කියනවා", "කට කතා කියනවා", "කතා පතුරුවනවා",
        "හුස්ම ගන්න දෙන්නේ නැහැ", "තනි කරනවා", "වෙන් කරනවා",
        "ගොඩ කරනවා", "දහුවා කරනවා", "අතපත කරනවා",
        "අවමන් කරනවා", "හෙළා දකිනවා", "පහත් කරනවා",
        
        # Commands/Insults related to Submission (Enhanced)
        "කට වහගන්න", "යනවා", "අවජාතක", "තෝ", "උඹට පිස්සු",
        "නොවැදගත්", "වටින්නේ නෑ", "කැත", "තඩි", "මැරෙනවා", 
        "අපායට", "පිස්සෙක්", "වැඩකට නැති", "තනියම",
        
        # Additional Insults
        "කට වහපන්", "හිස වහපන්", "මල්ලෙ", "පලයං", "යන්න",
        "නිකං යන්න", "මෙහෙන් යන්න", "යන තැන යන්න",
        "කවුරුත් කැමති නෑ", "යාළුවෝ නෑ", "තනිකමයි",
        "කවුරුත් ආදරෙයි කියන්නේ නෑ", "හැම දෙනා අතහරිනවා",
        "ජීවත්වෙන්න එපා", "මැරිලා යන්න", "ඉපදුනට වඩා හොඳයි",
        "වැඩක් නැති එකෙක්", "නිකං ඉන්න තැනක්", "බරක්",
        "නොපැහැදිලි", "අපවත්", "විකාර", "විකලාංග", "වැරදි",
        "මෝඩ", "පිස්සෙක්", "ගොන්නෙක්", "ලජ්ජා විරහිත"
    ]
    
    # Profanity/Slurs (Massively Enhanced)
    PROFANITY_SINHALA = [
        # General Insults/Slurs
        "බල්ලා", "බල්ලි", "ගොනා", "මෝඩයා", "පිස්සා", 
        "පිස්සු", "ලොන්ත", "වල්", "නපුරු", "බූරුවා", "හිපාට", 
        "ගොබ්බයා", "වඳුරා", "අලියා", "ඌරා", "නාකි",
        
        # Additional Animal-Based Insults
        "බල්ලෙක්", "බල්ල මෝඩයා", "ගොන් බල්ලා", "උඹ බල්ලෙක්",
        "හරකා", "හරක් බල්ලා", "එළුවා", "එළුගොනා", "බැට්ලුවා",
        "මී හරකා", "ගොනා කපු", "මූත්‍රා", "විකාර", "හීනෙක්",
        "රිලවුවා", "වල් බල්ලා", "හතරැස් බල්ලා",
        
        # Extreme Profanity (Direct Translations/Concepts)
        "හුකනවා", "පොන්නයා", "ගූ", "කැරි", "චූ",
        "අම්මණ්ඩි", "කුපාඩි", "අසහන", "අවජාතක", "බැල්ලි", 
        "බේබදු", "වේසි", "වෛශ්‍යාව", "ගණිකාව",
        
        # Massively Enhanced Sinhala Profanity
        "හුත්තා", "හුත්තෙක්", "හුකපු", "හුකන්න", "හුත්තිගේ",
        "පොන්න", "පොන්නි", "පොන්නකෝ", "පොන්න කැරියා",
        "පක", "පකයා", "පකේ", "පකෙක්", "පක කොල්ලා",
        "කැරිය", "කැරි පුතා", "කැරි බල්ලා", "කැරි හුත්තා",
        "චූතියා", "චූට්", "චූටි පොන්නයා", "චූටි හුත්තා",
        "අම්ම හුකනවා", "අම්මට හුකන්න", "අම්මා රිකන්න",
        "තාත්ත හුකනවා", "තාත්තාට", "නංගි හුකනවා",
        "අක්කි හුකනවා", "මල්ලි හුකනවා", "අයියා හුකනවා",
        "කොල්ලා හුකන්න", "තෝ හුකන්න", "උඹ හුකනවා",
        "ගූ කනවා", "ගූ කන", "ගූ කන්න යන්න", "ගූ කන කොල්ලා",
        "ගූ බල්ලා", "ගූ ටික", "ගූතර", "ගූවෙන් වැඩ",
        "කැරි බොනවා", "කැරි බොන", "කැරි බොන බල්ලා",
        "මෝඩ හුත්තා", "ලොකු හුත්තා", "පොඩි හුත්තා",
        
        # Sexual/Gendered Insults
        "වේසි", "වේසිව", "වේස්", "වැස්ස", "පුකේ වේස්",
        "හුකන වේස්", "වේසි කාර්යය", "වේසිකම්", "ගණිකා",
        "බඩු වේස්", "රෑ වේස්", "වේස් පුතා", "වේස් දරුවා",
        "රන්ඩි", "පත්තර", "හිපාට", "හිපාට වේස්",
        "සෙල්ලම්", "රස වල", "ගස්මන්", "ජඩ",
        
        # Family-Based Insults (Very Offensive)
        "අම්මගේ", "තාත්තගේ", "නංගිගේ", "අක්කිගේ", "අයියගේ",
        "හුත්ති පුතා", "වේසි පුතා", "හරක් දරුවා", "බල්ලගේ පුතා",
        "අම්මා පොන්නි", "තාත්තා හුත්තා", "නංගි වේසි",
        
        # Body Parts & Functions (Offensive)
        "පුකේ", "පුක", "පුක කනවා", "පුක ලෙවනවා",
        "පුක පාරක්", "පුකටත්", "පුකෙන්", "පුක දෙනවා",
        "බාල", "බල්ලි", "පයිය", "පයියක්", "පයි කොල්ලා",
        "තන", "තන් දෙක", "කිරි", "කිරි කොළ",
        
        # Mental/Intellectual Insults (Enhanced)
        "මෝඩයා", "පිස්සා", "ගොබ්බයා", "මානසික", "විකාර",
        "ඔලුවේ නෑ", "බුද්ධිය නෑ", "හිස හිස්", "හිස ගහ",
        "මොළේ හිස්", "මොළේ ගූ", "මොළයක් නෑ", "අන්ධ",
        "බිහිරි", "බිහිරි බල්ලා", "අන්ධ හුත්තා"
    ]

# Flask API Configuration
class FlaskConfig:
    SECRET_KEY = os.environ.get('SECRET_KEY', 'audio-threat-detection-secret-key-2024')
    DEBUG = os.environ.get('FLASK_DEBUG', 'False').lower() == 'true'
    HOST = os.environ.get('FLASK_HOST', '127.0.0.1')
    PORT = int(os.environ.get('FLASK_PORT', 5002))
    
    # CORS Settings
    CORS_ORIGINS = ['http://localhost:8000', 'http://127.0.0.1:8000']

# Noise Profiling Configuration
class NoiseConfig:
    ADAPTIVE_THRESHOLD = True
    NOISE_FLOOR_SAMPLES = 50
    NOISE_UPDATE_INTERVAL = 10  # seconds
    SNR_MINIMUM = 12  # dB - Increased from 10 to reduce false positives from ambient noise

# Create directories
os.makedirs(MODELS_DIR, exist_ok=True)
os.makedirs(LOGS_DIR, exist_ok=True)

