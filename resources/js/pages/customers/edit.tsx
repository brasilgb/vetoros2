import CustomerForm from './form';
type CustomerFormData = Record<string, string | number | boolean | null> & {
    id?: number;
    type?: string;
};

export default function CustomersEdit({
    customer,
}: {
    customer: CustomerFormData;
}) {
    return <CustomerForm customer={customer} editing />;
}
