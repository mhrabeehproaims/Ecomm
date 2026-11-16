'use client';

import { useId, useRef, useState } from 'react';


export function AboutTabs({ tabs }: { tabs: { title: string; body: string }[] }) {
  const [selected, setSelected] = useState(0);
  const id = useId();
  const buttons = useRef<(HTMLButtonElement | null)[]>([]);
  return <div className="about-tabs">
    <div role="tablist" aria-label="About Vanthara">{tabs.map((tab, index) => <button key={tab.title} ref={element => { buttons.current[index] = element; }} type="button" role="tab" id={`${id}-tab-${index}`} aria-controls={`${id}-panel-${index}`} aria-selected={selected === index} tabIndex={selected === index ? 0 : -1} onClick={() => setSelected(index)} onKeyDown={event => {
      let next = index;
      if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
      else if (event.key === 'ArrowLeft') next = (index - 1 + tabs.length) % tabs.length;
      else if (event.key === 'Home') next = 0;
      else if (event.key === 'End') next = tabs.length - 1;
      else return;
      event.preventDefault(); setSelected(next); buttons.current[next]?.focus();
    }}>{tab.title}</button>)}</div>
    {tabs.map((tab, index) => <div key={tab.title} role="tabpanel" id={`${id}-panel-${index}`} aria-labelledby={`${id}-tab-${index}`} hidden={selected !== index} tabIndex={0}>{tab.body.split('\n').map((paragraph, i) => <p key={i}>{paragraph}</p>)}</div>)}
  </div>;
}
