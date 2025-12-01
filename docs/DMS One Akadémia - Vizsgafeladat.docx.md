# DMS One Fejlesztői Akadémia \- Vizsgafeladat

### **A "Jóváhagyási folyamat" Vertical Slice Implementációja**

A vizsgafeladat egy Event Storming modellen alapuló, kritikus üzleti folyamat – a „Jóváhagyási folyamat” – egyetlen, de a maga nemében teljes vertikális szeletének (vertical slice) implementálása.

A cél nem a teljes modell leprogramozása, hanem egyetlen üzleti use case megvalósítása a Domain-Driven Design (DDD) elveit követve, érintve az összes architekturális réteget.

#### **A megvalósítandó use case:**

**"Egy ügyintéző benyújt egy számlát, ami ezzel automatikusan átkerül a feletteséhez jóváhagyásra."**

A feladatotok, hogy egy kulcsfontosságú üzleti folyamatot implementáljatok a megadott modell alapján. A kiindulási pont egy Event Storming modell, ami két Bounded Contextet azonosít: a **Számlabefogadást** és a **Jóváhagyást**.

A folyamat lépései a következők:

A kiindulási pont: Az Event Storming Modell

A modell két Bounded Context azonosít: a `Számlabefogadást` és a `Jóváhagyást`.

Kontextus 1: `Számlabefogadás` 

* A felelőssége: Nyers adatokból érvényes, befogadott számlát létrehozni.  
* Modell elemei:  
  * Parancs (Command): `Számla Benyújtása`   
  * Aggregátum (Aggregate): `Számla`   
  * Domén Esemény (Domain Event): `Számla Benyújtva` 

Kontextus 2: `Jóváhagyás` 

* A felelőssége: A befogadott számlák jóváhagyási vagy elutasítási folyamatának menedzselése.  
* Modell elemei:  
  * Parancs (Command): `Jóváhagyási Folyamat Elindítása`, `Számla Jóváhagyása`, `Számla Elutasítása`  
  * Aggregátum (Aggregate): `Jóváhagyás`   
  * Domén Esemény (Domain Event): `Jóváhagyási Folyamat Elindítva`, `Számla Jóváhagyva`, `Számla Elutasítva`

A Kontextusokat összekötő szabály (Policy / Process Manager):

* Szabály (Policy): Amikor `Számla Benyújtva` történik, akkor a rendszer elindítja a `Jóváhagyási Folyamatot` (`Jóváhagyási Folyamat Elindítása` paranccsal).

### **Elvárások**

**Kódminőség és Architektúra:**

* **Domain-Driven Design:** Az implementációnak követnie kell a DDD alapelveit (Aggregátumok, Domén Események, Parancsok).  
* **Clean Code:** A kód legyen tiszta, olvasható és karbantartható.  
* **Modern PHP:** Használjátok a PHP 8.4 modern elemeit (pl. `readonly properties`, `Enums`).  
* **API:** A folyamatot egy vékony REST API végponton keresztül kell elindítani, ami a külvilág és a doménmodell közötti kapuként szolgál.

**Tesztelés:**

* **80% tesztlefedettség**  
* **Unit Tesztek:** Az üzleti logika (pl. az aggregátumok viselkedése) legyen lefedve unit tesztekkel.  
* **Integrációs Teszt:** Készüljön egy integrációs teszt, ami a teljes "vertikális szeletet" ellenőrzi: a parancs beérkezésétől a Policy lefutásán át a második aggregátum létrejöttéig.

**Hibakezelés és monitoring:**

* **Hibakezelés:** Az aggregátumok szintjén, `DomainException`\-ök segítségével kell kezelni az érvénytelen állapotváltozási kísérleteket (pl. ha egy parancs érvénytelen adatot tartalmaz).  
* **Logolás:** A Policy-ban legyen logolás, hogy az automatizált folyamatok láncolata követhető és visszakereshető legyen.

**Konténerizáció:**

* **Docker:** Az egész alkalmazásnak egy Docker konténerben kell futnia.  
* **Docker Compose:** Egy `docker-compose.yml` fájl segítségével kell biztosítani az alkalmazás egyszerű indíthatóságát és a konzisztens fejlesztői környezetet.

### **Határidők**

| Esemény | Dátum | Időpont | Megjegyzés |
| :---- | :---- | :---- | :---- |
| **Képzés vége** | 2025\. november 28\. (Péntek) | \- | A feladat kiküldése. |
| **Leadási határidő** | **2025\. december 14\. (Vasárnap)** | **23:59 CET** | Két teljes hét áll rendelkezésre. |

A megoldásokat kérjük egy ZIP-fájlban vagy egy Git tároló linkjén (pl. GitHub) keresztül megosztani, a feladat indításához és teszteléséhez szükséges információkkal (README.md) ellátva: [judit.halasz@stylersgroup.com](mailto:judit.halasz@stylersgroup.com) és apro.janos@evista.hu.

Sok sikert és inspiráló kódolást kívánunk\!

Üdvözlettel,

Stylers Group csapata

