import "./bootstrap";

import {
    createIcons,
    LayoutGrid,
    Wallet,
    Bell,
    Users,
    ChevronLeft,
    Menu,
    X,
    CircleCheck,
    Pencil,
    Trash,
    AlertTriangle,
    Lock,
    Contact,
    Eye,
} from "lucide";

const icons = {
    LayoutGrid,
    Users,
    Wallet,
    Bell,
    ChevronLeft,
    Menu,
    X,
    CircleCheck,
    Pencil,
    Trash,
    AlertTriangle,
    Lock,
    Contact,
    Eye,
};

createIcons({ icons });

document.addEventListener("livewire:init", () => {
    Livewire.hook("morph.updated", () => createIcons({ icons }));
});
