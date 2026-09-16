import * as React from 'react';
import { cn } from '@/lib/utils';

type TabsContextValue = {
    value: string;
    setValue: (value: string) => void;
};

const TabsContext = React.createContext<TabsContextValue | null>(null);

function useTabsContext(component: string): TabsContextValue {
    const context = React.useContext(TabsContext);
    if (!context) {
        throw new Error(`<${component} /> must be used inside <Tabs />`);
    }

    return context;
}

type TabsProps = React.ComponentProps<'div'> & {
    value?: string;
    defaultValue?: string;
    onValueChange?: (value: string) => void;
};

function Tabs({ value, defaultValue, onValueChange, className, children, ...props }: TabsProps) {
    const [internalValue, setInternalValue] = React.useState(defaultValue ?? '');
    const isControlled = value !== undefined;
    const current = isControlled ? value : internalValue;

    const setValue = React.useCallback(
        (next: string) => {
            if (!isControlled) {
                setInternalValue(next);
            }
            onValueChange?.(next);
        },
        [isControlled, onValueChange],
    );

    return (
        <TabsContext.Provider value={{ value: current, setValue }}>
            <div data-slot="tabs" className={cn('flex flex-col gap-4', className)} {...props}>
                {children}
            </div>
        </TabsContext.Provider>
    );
}

function TabsList({ className, ...props }: React.ComponentProps<'div'>) {
    return (
        <div
            data-slot="tabs-list"
            role="tablist"
            className={cn('bg-muted text-muted-foreground inline-flex h-9 w-fit items-center justify-center rounded-lg p-1', className)}
            {...props}
        />
    );
}

function TabsTrigger({ className, value, ...props }: React.ComponentProps<'button'> & { value: string }) {
    const { value: active, setValue } = useTabsContext('TabsTrigger');
    const isActive = active === value;

    return (
        <button
            type="button"
            data-slot="tabs-trigger"
            role="tab"
            aria-selected={isActive}
            data-state={isActive ? 'active' : 'inactive'}
            onClick={() => setValue(value)}
            className={cn(
                "text-foreground dark:text-muted-foreground inline-flex h-[calc(100%-1px)] flex-1 items-center justify-center gap-1.5 rounded-md border border-transparent px-2 py-1 text-sm font-medium whitespace-nowrap transition-[color,box-shadow] focus-visible:outline-1 disabled:pointer-events-none disabled:opacity-50 data-[state=active]:bg-background data-[state=active]:shadow-sm dark:data-[state=active]:bg-input/30 dark:data-[state=active]:border-input",
                className,
            )}
            {...props}
        />
    );
}

function TabsContent({ className, value, ...props }: React.ComponentProps<'div'> & { value: string }) {
    const { value: active } = useTabsContext('TabsContent');

    if (active !== value) {
        return null;
    }

    return <div data-slot="tabs-content" role="tabpanel" className={cn('flex-1 outline-none', className)} {...props} />;
}

export { Tabs, TabsList, TabsTrigger, TabsContent };
