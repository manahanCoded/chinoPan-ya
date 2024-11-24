const fetchSpells = async () => {
    const res = await fetch('https://potterapi-fedeperin.vercel.app/en/spells')
    const spells = await res.json()

    spells.forEach(element => {
        return element.spell
    });
}

fetchSpells()