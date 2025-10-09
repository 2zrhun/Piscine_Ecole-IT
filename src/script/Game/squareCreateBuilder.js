export function createSquareBuilder(options = {}) {
    const {
        id = 'fixed-square',
        pointerEvents = 'auto',
        imageSrc
    } = options;

    const existing = document.getElementById(id);
    if (existing) return existing;

    const choiceBuilder = document.createElement('div');
    choiceBuilder.id = id;
    Object.assign(choiceBuilder.style, {
        position: 'fixed',
        left: `16px`,
        bottom: `16px`,
        width: `100px`,
        height: `100px`,
        background: "white",
        border: `3px solid black`,
        boxSizing: 'border-box',
        zIndex: String(9999),
        cursor: 'pointer',
        display: 'flex',
        alignItems: 'center',
        justifyContent: 'center',
        pointerEvents,
    });

    if (imageSrc) {
        const img = document.createElement('img');
        img.src = imageSrc;
        Object.assign(img.style, {
            width: '80%',
            height: '80%',
            objectFit: 'contain',
            pointerEvents: 'none',
        });
        choiceBuilder.appendChild(img);
    }

    const pageBuilder = document.createElement('div');
    Object.assign(pageBuilder.style, {
        position: 'fixed',
        left: `16px`,
        bottom: choiceBuilder.style.bottom,
        width: `100px`,
        height: '0px',
        background: 'white',
        border: `2px solid black`,
        boxSizing: 'border-box',
        overflow: 'hidden',
        zIndex: String(9998),
        transition: `height 0.5s ease`,
    });
    document.body.appendChild(pageBuilder);

    let isOpen = false;
    choiceBuilder.addEventListener('click', () => {
        pageBuilder.style.height = isOpen ? '0px' : `500px`;
        isOpen = !isOpen;
    });

    document.body.appendChild(choiceBuilder);
    return choiceBuilder;
}
